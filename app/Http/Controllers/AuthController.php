<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Login user
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'message' => 'Invalid credentials'
                    ]
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'Could not create token'
                ]
            ], 500);
        }

        $user = auth()->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ],
            'token' => $token,
            'meta' => [
                'status' => 'success',
                'message' => 'success login'
            ]
        ]);
    }

    // Mendapatkan data pengguna yang sedang login
    public function me()
    {
        $user = JWTAuth::user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'meta' => [
                'status' => 'success',
                'message' => 'success fetch profile'
            ]
        ]);
    }

    // Logout user
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Logout successful'
            ]
        ]);
    }
}
