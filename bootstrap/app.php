<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware Global (jika ada yang harus jalan di semua request)
        // $middleware->append(\App\Http\Middleware\Authenticate::class);

        // Alias Middleware — bisa dipakai di route dengan nama pendek
        $middleware->alias([
            'auth.jwt'   => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.auth'   => \App\Http\Middleware\JWTAuthentication::class, // optional, jika kamu pakai custom middleware

            // Role-based Middleware
            'admin'      => \App\Http\Middleware\AdminMiddleware::class,
            'staff'      => \App\Http\Middleware\StaffMiddleware::class,
            'manajer'    => \App\Http\Middleware\ManajerMiddleware::class,
        ]);
    })
    ->withProviders([
        // JWTAuth Service Provider
        \PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling here (optional)
    })
    ->create();
