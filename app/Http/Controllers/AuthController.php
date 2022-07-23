<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        if (!Auth::attempt($credentials)) {
            return responseJson(false, 'login failed', null, 401);
        }
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('token-'.$user->username)->plainTextToken;
        return responseJson(true, 'login success', ['token' => $token], 200);
    }
}
