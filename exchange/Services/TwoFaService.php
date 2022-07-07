<?php

namespace App\Services;

use App\Models\TwoFactorUserSession;
use PragmaRX\Google2FALaravel\Google2FA;

class TwoFaService
{
    private Google2FA $google2FA;

    public function __construct(Google2FA $google2FA)
    {
        $this->google2FA = $google2FA;
    }

    /** verify Google2FA key */
    public function isVerifyGoogle2FAKey(string $userGoogle2faSecret, string $userInputSecret): bool
    {
        // 4 keys (respectively 2 minutes) past and future
        $window = 4;
        return $this->google2FA->verifyKey($userGoogle2faSecret, $userInputSecret, $window);
    }

    public function getTwoFactorUserSession(string $userSession): TwoFactorUserSession
    {
        $userInfo = TwoFactorUserSession::query()
            ->with(['user'])
            ->where('user_session', $userSession)
            ->first();

        if (!$userInfo) {
            abort(401, __('User not found'));
        }

        return $userInfo;
    }

    public function generateGoogleSecretKey(int $length = 16): string
    {
        return $this->google2FA->generateSecretKey($length);
    }

}
