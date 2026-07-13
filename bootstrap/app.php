<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust reverse-proxy headers (Nginx, Apache, load-balancers)
        // Set TRUSTED_PROXIES=* in .env for open trust, or restrict to a specific IP.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );
        $middleware->web(append: [
            \App\Http\Middleware\DbSessionContextMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'login',
            'otp/send',
            'otp/verify',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'main_system' => \App\Http\Middleware\EnsureMainSystem::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
