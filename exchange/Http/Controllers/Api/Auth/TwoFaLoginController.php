<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\Auth\LoginUserEvent;
use App\Http\Requests\Auth\TwoFaLoginRequest;
use App\Modules\TFA\TFAFactory;
use App\Services\UserAuthService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;


class TwoFaLoginController extends BaseAuthController
{

    public function __invoke(TwoFaLoginRequest $request, UserAuthService $userService): JsonResource|JsonResponse
    {
        $data = $request->validated();

        $user = $userService->getUserBySession($data['user_session']);

        if ($user->isEnabled2FA()) {
            TFAFactory::initAuthentication($user)->checkCode($data['code']);
            //write to log, success user is logged in
            event(new LoginUserEvent($user->id, $request->ip()));
            return $this->generateTokens($user, $user->primary_email);
        }

        abort(403, __('Access denied'));
    }
}

