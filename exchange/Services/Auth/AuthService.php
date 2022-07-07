<?php

namespace App\Services\Auth;

use App\Contracts\AuthTokenGenerator;
use App\Contracts\InvalidLoginAccessAttempts;
use App\Events\Auth\FailedLoginEvent;
use App\Http\Traits\ValidateInvalidLoginAttempts;
use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\Client;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;


class AuthService implements AuthTokenGenerator, InvalidLoginAccessAttempts
{
    use ValidateInvalidLoginAttempts;

    public function auth(string $login, string $password): User
    {
        if (empty($login) || empty($password)) {
            $this->incInvalidAttempts();
            event(new FailedLoginEvent(Request::ip()));
            abort(401, 'Fail login');
        }

        $user = User::where('username', $login)
            ->orWhere('primary_email', $login)
            ->orWhere('primary_phone', $login)
            ->first();

        if (!$user) {
            event(new FailedLoginEvent(Request::ip()));
            $this->incInvalidAttempts();
            abort(404, __('Incorrect username/email or password'));
        }

        if ($user->isDisabled()) {
            abort(
                401,
                __(
                    "messages.security_config.validation.login_attempts",
                    [
                        'supportRoute' => route('auth.login'),
                        'phone' => config('auth.hashtex.support.phone')
                    ]
                )
            );
        }

        if (!Hash::check($password, $user->password)) {
            event(new FailedLoginEvent(Request::ip(), $user->id));
            $this->incInvalidAttempts($user->id);
            abort(401, __('Incorrect username/email or password'));
        }
        //after success login, reset attempts for login
        $this->resetUserInvalidAttempts();
        //2FA off
        if (!$user->isEnabled2FA()) {
            Auth::login($user);
        }
        return $user;
    }

    public function getSocialAccountLogin(string $provider, string $accessToken): SocialUser
    {
        return Socialite::driver($provider)->userFromToken($accessToken);
    }

    public function userBySocialProvider(string $provider, string $providerId): int|null
    {
        $providerData = SocialLogin::select('user_id')
            ->with('user')
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
        return ($providerData) ? $providerData->user_id : null;
    }

    public function socialRegister(User $user, string $providerId, string $provider): SocialLogin
    {
        return SocialLogin:: create(
            [
                'user_id' => $user->id,
                'provider_id' => $providerId,
                'provider' => $provider,
            ]
        );
    }


    public function verifyEmail(string $email, string $hash)
    {
        $user = User::wherePrimaryEmail($email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            abort(403, __('Hash already verified'));
        }
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, __('Hash is not correct'));
        }
        $user->markEmailAsVerified();
    }

    /**
     * @param array $data
     * @param string $type
     * @return ClientResponse
     */
    public function generateTokens(array $data, string $type): ClientResponse
    {
        $data = array_merge($data, $this->getClientData(), ['grant_type' => $type, 'scope' => '']);
        return Http::asForm()->post(config('app.internal_url') . '/oauth/token', $data);
    }

    private function getClientData(): array
    {
        $client = Client::where('name', config('app.name') . ' Password Grant Client')->latest()->first();
        if (!$client) {
            abort(404, __("Password grant client name not found in db"));
        }
        return [
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ];
    }
}
