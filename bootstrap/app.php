<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan middleware global
        $middleware->append(\App\Http\Middleware\Authenticate::class);

        // Daftarkan alias middleware
        $middleware->alias([
            'auth.jwt' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.auth' => \App\Http\Middleware\JWTAuthentication::class,
        ]);
    })
    ->withProviders([
        // Register JWT Service Provider
        \PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
