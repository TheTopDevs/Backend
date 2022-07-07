<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\AuthTokenGenerator;
use App\Events\Auth\FailedLoginEvent;
use App\Events\Auth\LoginUserEvent;
use App\Events\Auth\UserRegisteredEvent;
use App\Http\Requests\Auth\SocialiteLoginRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class SocialLoginController extends BaseAuthController
{

    public function __construct(
        protected AuthTokenGenerator $tokenGenerator,
        private AuthService $authService,
        private UserService $userService,
    ) {
        parent::__construct($tokenGenerator);
    }

    public function __invoke(SocialiteLoginRequest $request,) {
        try {
            $socialUser = $this->authService->getSocialAccountLogin($request->provider, $request->accessToken);
        } catch (ClientException $exception) {
            //write to log, user is failed logged in
            event(new FailedLoginEvent($request->ip()));
            abort(401, __('auth.token.incorrect'));
        }

        $email = ($request->email) ?? $socialUser->email;

        if(!isset($email)) {
            abort(406, __('required.email'));
        }

        $userId = $this->authService->userBySocialProvider($request->provider, $socialUser->getId());

        $user = ($userId) ? User::find($userId) : $this->createOrAssignUser(
            $email,
            $socialUser->getId(),
            $request->provider
        );

        //write to log, success user is logged in
        event(new LoginUserEvent($user->id, $request->ip()));

        return $this->generateTokens($user, $email);
    }

    private function createOrAssignUser(string $email, string $providerId, string $provider): User
    {
        $user = $this->userService->getUserByEmail($email);

        if (!$user) {
            event(new UserRegisteredEvent($user, Request::ip()));
            $user = $this->userService->register($email, Config::get('auth.default_social_user_password'));
        }

        $this->authService->socialRegister($user, $providerId, $provider);

        return $user;
    }
}
