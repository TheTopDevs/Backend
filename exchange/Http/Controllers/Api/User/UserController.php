<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ConfirmationEmailChangeRequest;
use App\Http\Requests\User\ConfirmationPhoneChangeRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    public function userUpdate(UserUpdateRequest $request)
    {
        $user = Auth::user();
        $response = [];

        if ($email = $request->changedPrimaryEmail($user)) {
            $response['emailHash'] = $this->userService->createChangeHashCache($user, $email);
        }

        if ($phone = $request->changedPrimaryTel($user)) {
            $response['phoneCode'] = $this->userService->createChangeSMSCodeCache($user, $phone);
        }

        $user->alternate_email = $request->alterEmail();
        $user->alternate_phone = $request->alterTel();
        $user->save();

        $response['user'] = UserResource::make($user);

        return response()->json($response);
    }

    public function confirmPhone(ConfirmationPhoneChangeRequest $request)
    {
        $user = Auth::user();

        $cache = $this->userService->getSMSCodeFromCache($user);
        if ($cache['code'] != $request->code) {
            abort(401, __('phone.code.incorrect'));
        }

        $user->primary_phone = $cache['phone'];
        $user->save();

        return UserResource::make($user);
    }

    public function resendSMSCode()
    {
        $user = Auth::user();

        $cache = $this->userService->getSMSCodeFromCache($user);
        if (!isset($cache['phone'])) {
            abort(401, __('phone.code.not.exist'));
        }

        $code = $this->userService->createChangeSMSCodeCache($user, $cache['phone']);

        return response(['code' => $code]);
    }

    public function confirmEmail(ConfirmationEmailChangeRequest $request)
    {
        $user = Auth::user();

        $cache = $this->userService->getEmailHashFromCache($user);
        if ($request->hash != $cache['hash']) {
            abort(401, __('email.hash.incorrect'));
        }

        $user->primary_email = $cache['email'];
        $user->save();

        return UserResource::make($user);
    }

    public function resendEmailHash()
    {
        $user = Auth::user();

        $cache = $this->userService->getEmailHashFromCache($user);
        if (!isset($cache['email'])) {
            abort(401, __('email.hash.not.exist'));
        }

        $code = $this->userService->createChangeHashCache($user, $cache['email']);

        return response(['code' => $code]);
    }
}
