<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user && $user->role === 'staff') {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized (Staff only)'], 403);
    }
}
