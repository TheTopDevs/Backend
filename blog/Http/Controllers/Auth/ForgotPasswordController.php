<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController  extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $email = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()->where('email', '=', $email)->first();

        if (!$user) return response()->json(['status' => false, 'message' => 'User with this email not found']);

        $remember_token = bcrypt(md5(uniqid()));
        $user->update(['remember_token' => $remember_token]);
        Mail::to($email)->send(new ForgotPassword($remember_token));

        return response()->json(['status' => true, 'message' => 'Email was successfully sent']);
    }
}
