<?php

namespace App\Http\Traits;

use App\Events\User\UserDisabledEvent;
use App\Models\SecurityConfig;
use App\Services\Admin\SecurityConfigService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

trait ValidateInvalidLoginAttempts
{
    protected string $cacheKey = "invalid_login_attempts";

    public function incInvalidAttempts(int $userId = 0): int
    {
        $securityConfig = $this->getConfigParams();
        $counter = Cache::get($this->getCacheKey(), 0);
        $duration = (int)$securityConfig->param2_value * 60;//in sec
        Cache::add($this->getCacheKey(), $counter, $duration);
        $result = Cache::increment($this->getCacheKey());
        $counter++;
        //disable user if limit attempts is out
        if ($userId > 0 && $counter >= (int)$securityConfig->param1_value) {
            event(new UserDisabledEvent($userId, Request::ip()));
        }
        return $result;
    }

    protected function getCacheKey(): string
    {
        return $this->cacheKey . ':' . getUserRequestId();
    }

    public function isBlockedUserForLogin(): bool
    {
        $securityConfig = $this->getConfigParams();
        $counter = (int)Cache::get($this->getCacheKey(), 0);
        if ($this->isEnabledForChecking() && $counter >= (int)$securityConfig->param1_value) {
            return true;
        }
        return false;
    }

    public function resetUserInvalidAttempts(): bool
    {
        return Cache::forget($this->getCacheKey());
    }

    protected function isEnabledForChecking(): bool
    {
        return SecurityConfigService::isEnabledParam(SecurityConfig::INVALID_ACCESS_ATTEMPTS);
    }

    protected function getConfigParams(): SecurityConfig
    {
        return SecurityConfigService::getParamByKey(SecurityConfig::INVALID_ACCESS_ATTEMPTS);
    }
}
