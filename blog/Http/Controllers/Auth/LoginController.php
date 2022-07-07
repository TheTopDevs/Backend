<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginFormRequest $request, AuthService $authService): JsonResponse|AuthResource
    {
        $data = $request->validated();
        $token = $authService->login($data);

        return ($token)
            ? AuthResource::make($token)
            : response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    }
}
