<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


class AuthService
{
    /** @var User $user */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    private function createToken()
    {
        if (Auth::check()) {
            $token = Auth::user()->createToken(config('app.name'));
            $token->token->expires_at = now()->addMonth();
            $token->token->save();
        }

        return $token ?? null;
    }

    /**
     * @param $credentials
     * @return false|null
     */
    public function login($credentials)
    {
        return Auth::attempt($credentials) ? $this->createToken() : false;
    }

}
