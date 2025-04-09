<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ManajerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::user();

        if ($user && $user->role === 'manajer') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
