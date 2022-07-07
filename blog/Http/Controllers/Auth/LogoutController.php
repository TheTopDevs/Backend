<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status'  => 'ok',
            'message' => 'You are successfully logged out',
        ]);
    }
}
