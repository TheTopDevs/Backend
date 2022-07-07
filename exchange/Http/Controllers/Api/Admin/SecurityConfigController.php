<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfigService\UpdateConfigRequest;
use App\Http\Resources\Admin\SecurityConfig\ConfigListResource;
use App\Services\Admin\SecurityConfigService;

class SecurityConfigController extends Controller
{
    public function __construct(private SecurityConfigService $configService)
    {
    }

    public function getConfig()
    {
        return ConfigListResource::make(SecurityConfigService::INDEX_GROUP);
    }

    public function setConfig(UpdateConfigRequest $request)
    {
        $data = $request->validated();
        $this->configService->batchUpdateConfig($data);
        return response()->json(['data' => ['success' => true]]);
    }
}
