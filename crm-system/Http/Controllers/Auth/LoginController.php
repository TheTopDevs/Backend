<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /**
     * @OA\Post (
     *     tags={"Auth"},
     *     path="/api/auth/login/",
     *     summary="User login on website and get token",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="email",
     *                     required={"true"},
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     required={"true"},
     *                     default="password"
     *                 ),
     *                 example={"email": "alessandra.toy@example.net", "password": "password"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="login OK", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="401", description="You cannot sign with those credentials", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="The given data was invalid.", @OA\MediaType(mediaType="application/json")),
     * )
     * Handle the incoming request.
     *
     * @param LoginFormRequest $request
     * @param AuthService $authService
     * @return AuthResource|JsonResponse
     */
    public function __invoke(LoginFormRequest $request, AuthService $authService): JsonResponse|AuthResource
    {
        $data = $request->validated();
        $token = $authService->login($data);
        return $token
            ? AuthResource::make($token)
            : response()->json([
                'message' => 'You cannot sign with those credentials',
                'errors' => 'Unauthorised'
            ], 401);
    }

}
