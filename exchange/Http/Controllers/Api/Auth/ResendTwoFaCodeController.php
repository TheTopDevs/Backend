<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendTwoFaLoginRequest;
use App\Http\Resources\Auth\ResendTwoFaCodeResource;
use App\Services\UserAuthService;
use Illuminate\Http\Resources\Json\JsonResource;
use \Illuminate\Http\JsonResponse;

class ResendTwoFaCodeController extends Controller
{
    public function __invoke(ResendTwoFaLoginRequest $request, UserAuthService $userService): JsonResource|JsonResponse
    {
        $data = $request->validated();

        $user = $userService->getUserBySession($data['user_session']);

        //get new user_session
        $userSession = $userService->generateTwoFactorCode($user);

        return ResendTwoFaCodeResource::make($userSession);
    }
}
