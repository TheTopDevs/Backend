<?php

namespace App\Services\Admin;

use App\Models\SecurityConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecurityConfigService
{
    public const CACHE_TAG_NAME = "security_config";

    public const INDEX_GROUP = [
        'login' => [
            SecurityConfig::AUTOMATICALLY_DISABLE,
            SecurityConfig::INVALID_ACCESS_ATTEMPTS,
            SecurityConfig::AUTOMATICALLY_LOCK,
            SecurityConfig::AUTOMATICALLY_DELAY,
            SecurityConfig::CONCURRENT_SESSION,
            SecurityConfig::SESSION_TERMINATION,
            SecurityConfig::LOGIN_ATTEMPTS_BLOCK,
            SecurityConfig::FA_AUTHENTICATION_EMAIL,
            SecurityConfig::FA_AUTHENTICATION_PHONE
        ],
        'password' => [
            SecurityConfig::LIFETIME,
            SecurityConfig::LENGTH,
            SecurityConfig::MUST_CONTAIN,
            SecurityConfig::RESET,
        ],
        'log' => [
            SecurityConfig::ARCHIVE_LOG
        ]
    ];

    public static function getConfigParamsForGroup(string $groupConfig): array
    {
        if (isset(self::INDEX_GROUP[$groupConfig])) {
            return self::INDEX_GROUP[$groupConfig];
        }
        return [];
    }

    public static function getCacheKeyParam(string $key): string
    {
        return self::CACHE_TAG_NAME . ":" . $key;
    }

    public static function getParamByKey(string $key): mixed
    {
        return Cache::tags(self::CACHE_TAG_NAME)->rememberForever(
            self::getCacheKeyParam($key),
            function () use ($key) {
                return SecurityConfig::query()->where('key', '=', $key)->first();
            }
        );
    }

    public static function getParam(string $key): SecurityConfig
    {
        if (!self::existParamKey($key)) {
            abort(404, __("messages.security_config.not_found_cache_key", ['key' => $key]));
        }
        /** @var SecurityConfig $model */
        return self::getParamByKey($key);
    }

    public static function isEnabledParam(string $keyParam): bool
    {
        /** @var SecurityConfig $model */
        $model = self::getParam($keyParam);
        return $model->enable;
    }

    public static function getValueOfParam(string $key, string $nameParam = ""): int
    {
        $model = self::getParam($key);
        //by default get first value of params
        if (empty($nameParam) || $model->param1_name === $nameParam) {
            return $model->param1_value;
        }
        if ($model->param2_name === $nameParam) {
            return $model->param2_value;
        }
        abort(404, __("messages.security_config.not_found.param", ["nameParam" => $nameParam]));
    }

    public function setParamByKey(SecurityConfig $model): bool
    {
        return Cache::tags(self::CACHE_TAG_NAME)->forever(self::getCacheKeyParam($model['key']), $model);
    }

    public static function existParamKey(string $key): bool
    {
        return Cache::tags(self::CACHE_TAG_NAME)->has(self::getCacheKeyParam($key));
    }

    public function batchUpdateConfig(array $data): void
    {
        foreach ($data as $categoryNameConfig => $configValueList) {
            $paramsOfCategory = self::getConfigParamsForGroup($categoryNameConfig);
            if (!empty($paramsOfCategory)) {
                foreach ($configValueList as $keyNameParam => $configValue) {
                    if (!self::existParamKey($keyNameParam)) {
                        continue;
                    }
                    /** @var SecurityConfig $configModel */
                    $configModel = self::getParamByKey($keyNameParam);
                    $this->updateModelFromArray($configModel, $configValue);
                }
            }
        }
    }

    private function updateModelFromArray(SecurityConfig $configModel, array $configValue): void
    {
        if (isset($configValue['enable']) && !empty($configValue['enable'])) {
            $configModel->enable = $configValue['enable'];
            unset($configValue['enable']);
        }
        foreach ($configValue as $index => $value) {
            if ($configModel->param1_name === $index) {
                $configModel->param1_value = $value;
            }
            if ($configModel->param2_name === $index) {
                $configModel->param2_value = $value;
            }
        }
        $this->setParamByKey($configModel);
    }
}
