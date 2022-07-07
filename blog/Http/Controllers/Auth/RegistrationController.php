<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationFormRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function __invoke(RegistrationFormRequest $request, AuthService $authService): JsonResponse|AuthResource
    {
        $data = $request->validated();
        $user = $authService->register($data);

        return ($user)
            ? AuthResource::make($authService->login($data))
            : response()->json(['status' => false, 'message' => 'Registration failed'], 406);
    }
}
