<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::user();  // Mendapatkan pengguna yang sedang login

        // Memeriksa apakah role pengguna adalah admin
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}



