<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SettingUpdateRequest;
use App\Http\Resources\User\SettingResource;
use App\Services\User\SettingService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{

    public function __construct(
        protected UserService $userService,
        protected SettingService $settingService
    ) {
    }

    public function __invoke(SettingUpdateRequest $request)
    {
        $user = Auth::user();

        $this->userService->updateUser($user->id, $request->twoFactorWayConfig());

        $model = $this->settingService->updateOrCreateUserSetting($user->id, $request->allSettings());

        return SettingResource::make($model);
    }
}
