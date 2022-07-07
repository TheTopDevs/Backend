<?php

namespace App\Services;

use App\Models\TwoFactorUserSession;
use App\Models\User;
use Illuminate\Support\Str;

class UserAuthService
{
    private TwoFaService $twoFaService;

    public function __construct(TwoFaService $twoFaService)
    {
        $this->twoFaService = $twoFaService;
    }

    public function getUserBySession(string $userSession): User
    {
        //fin user by userSession
        $twoFactorUser = $this->twoFaService->getTwoFactorUserSession($userSession);

        $user = $twoFactorUser->user;

        if (!$twoFactorUser) {
            abort(404, __('User not found'));
        }
        //check expired user_session
        if (TwoFactorUserSession::isExpired($twoFactorUser->expired_at)) {
            abort(403, __('Your session is expired'));
        }

        return $user;
    }

    /**
     * Generate 6 digits MFA code for the User
     */
    public function generateTwoFactorCode(User $user, int $minutes = 5): string
    {
        //delete old data
        TwoFactorUserSession::query()->where('user_id', $user->id)->delete();

        //generate "user_session"
        $userTwoFactorAuth = $user->userTwoFactorAuth()
            ->create([
                'user_session' => Str::uuid(),
                'user_id' => $user->id,
                'expired_at' => now()->addMinutes($minutes)
            ]);

        //Auth with not Google2FA
        if ($user->two_factor_way !== User::TFA_GOOGLE) {
            //set new params
            $user->timestamps = false; //Dont update the 'updated_at' field yet
            $user->two_factor_code = random_int(100000, 999999);
            $user->two_factor_expires_at = now()->addMinutes($minutes);
            $user->save();
        }
        $user->userTwoFactorAuth->user_session = $userTwoFactorAuth->user_session;

        return $userTwoFactorAuth->user_session;
    }
}
