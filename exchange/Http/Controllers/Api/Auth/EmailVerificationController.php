<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Services\Auth\AuthService;

class EmailVerificationController extends Controller
{
    public function __invoke(EmailVerificationRequest $request, AuthService $authService)
    {
        $data = $request->validated();

        $authService->verifyEmail($data['email'], $data['hash']);

        return response()->json(['data' => ['message' => 'Success verification email']]);
    }
}
