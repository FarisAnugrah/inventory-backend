<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized (Admin only)'], 403);
    }
}
