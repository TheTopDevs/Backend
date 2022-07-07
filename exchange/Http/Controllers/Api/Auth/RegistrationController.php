<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\Auth\UserRegisteredEvent;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use Illuminate\Auth\Events\Registered;

class RegistrationController extends BaseAuthController
{
    public function __invoke(RegistrationRequest $request, UserService $userService, AuthService $authService)
    {
        $data = $request->validated();

        $user = $userService->register($data['email'], $data['password']);

        event(new UserRegisteredEvent($user, $request->ip()));

        return $this->generateTokens($user, $data['email'], $data['password']);
    }
}
