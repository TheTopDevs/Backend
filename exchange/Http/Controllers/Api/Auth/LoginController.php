<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\Auth\LoginUserEvent;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Models\SecurityLog;
use App\Models\User;
use App\Modules\TFA\TFAFactory;
use App\Services\UserAuthService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;


class LoginController extends BaseAuthController
{
    public function __invoke(LoginFormRequest $request, UserAuthService $userService): JsonResponse|JsonResource
    {
        $data = $request->validated();

        /** @var User $user */
        $user = $this->tokenGenerator->auth($data['login'], $data['password']);

        if ($user->isEnabled2FA()) {
            return TFAFactory::initAuthentication($user)->authenticate();
        }
        //write to log, success user is logged in
        event(new LoginUserEvent($user->id, $request->ip()));
        return $this->generateTokens($user, $data['login'], $data['password']);
    }
}
