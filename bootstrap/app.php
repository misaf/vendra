<?php

declare(strict_types=1);

use App\Http\Middleware\AddRequestContext;
use App\Http\Middleware\HandleStorefrontCors;
use App\Http\Middleware\SecureMcpTransport;
use App\Http\Middleware\UseRequestUrl;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS runs first so preflight requests are answered before routing.
        $middleware->prepend([
            HandleStorefrontCors::class,
            AddRequestContext::class,
            UseRequestUrl::class,
        ]);
        $middleware->append(SecureMcpTransport::class);

        // Trust the TLS-terminating proxy, the only peer that can reach the app.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->preventRequestForgery(except: [
            '/webhooks/coinpayments',
            '/webhooks/resend',
            '*/oauth/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
