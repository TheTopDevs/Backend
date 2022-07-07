<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LogoutController extends Controller
{
    /**
     * @OA\Post(
     *      tags={"Auth"},
     *      path="/api/auth/logout",
     *      summary="User logout",
     *      security={{"bearer": {}}},
     *      @OA\Response(response="200", description="object with the property status", @OA\MediaType(mediaType="application/json"))
     *  )
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status'  => 'ok',
            'message' => 'You are successfully logged out',
        ]);
    }

}
