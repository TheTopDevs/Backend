<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\AuthTokenGenerator;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\LoginTwoResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

class BaseAuthController extends Controller
{
    public const TYPE_PASSWORD = 'password';

    public function __construct(protected AuthTokenGenerator $tokenGenerator)
    {
    }

    protected function generateTokens(User $user, string $login, string $pass = ""): JsonResponse|JsonResource
    {
        $password = empty($pass) ? Config::get('auth.default_social_user_password') : $pass;

        $tokenResponse = $this->tokenGenerator
            ->generateTokens(['username' => $login, 'password' => $password], static::TYPE_PASSWORD)
            ->json();

        return LoginTwoResource::make($user)
            ->setAccessToken($tokenResponse['access_token'])
            ->setRefreshToken($tokenResponse['refresh_token'])
            ->setExpiresIn($tokenResponse['expires_in']);
    }
}
