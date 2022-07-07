<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class OAuthController extends BaseAuthController
{
    public const TYPE_REFRESH = 'refresh_token';

    public function refresh(Request $request): array
    {
        $response = $this->tokenGenerator
            ->generateTokens($request->only('refresh_token'), static::TYPE_REFRESH);

        if ($response->ok()) {
            return [
                'tokenType' => 'Bearer',
                'accessToken' => $response->json('access_token'),
                'refreshToken' => $response->json('refresh_token'),
                'expiresIn' => $response->json('expires_in'),
            ];
        }
        return [
            'error' => $response->json('error'),
            'errorDescription' => $response->json('error_description'),
            'hint' => $response->json('hint'),
            'message' => $response->json('message'),
        ];
    }

}
