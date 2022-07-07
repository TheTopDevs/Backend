<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection(User::getAllForMainPage($request->all()));
    }

    /**
     * @return UserResource
     */
    public function show(): UserResource
    {
        return UserResource::make(Auth::user());
    }

    /**
     * @param UpdateUserRequest $request
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        return response()->json(['status' => Auth::user()->update($data)]);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()->where('email', '=', $email)->first();
        return response()->json(['email_exists' => (bool)$user]);
    }

    /**
     * @param UpdatePasswordRequest $request
     * @param AuthService $authService
     * @return JsonResponse|AuthResource
     */
    public function updatePassword(UpdatePasswordRequest $request, AuthService $authService): JsonResponse|AuthResource
    {
        $data = $request->validated();

        $user = User::query()->where('remember_token', '=', $data['token'])->first();

        if (!$user) return response()->json(['status' => false, 'message' => 'User not found'], 406);

        $user->update([
            'password' => bcrypt($data['password']),
            'remember_token' => null
        ]);

        $credentials = ['email' => $user->email, 'password' => $data['password']];
        return AuthResource::make($authService->login($credentials));
    }
}
