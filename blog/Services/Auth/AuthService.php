<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param User $user
     * @return PersonalAccessTokenResult
     */
    private function makeTokenForUser(User $user): PersonalAccessTokenResult
    {
        $token = $user->createToken(config('app.name'));
        $token->token->expires_at = now()->addMinutes(config('app.PASSPORT_TOKEN_LIFETIME'));
        $token->token->save();
        return $token;
    }

    private function createTokenAuthUser()
    {
        return Auth::check() ? $this->makeTokenForUser(Auth::user()) : null;
    }

    public function login($credentials)
    {
        $loginData = ['email' => $credentials['email'], 'password' => $credentials['password']];
        return Auth::attempt($loginData) ? $this->createTokenAuthUser() : false;
    }

    public function register(array $data)
    {
        $oldUser = User::query()->where('email', $data['email'])->first();
        return $oldUser ? false : $this->makeUser($data);
    }

    private function makeUser(array $data): User
    {
        return $this->user->create($data);
    }

}
