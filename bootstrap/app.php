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
        // CORS first: storefronts are served on customer domains but fetch from
        // the canonical api.<base> host, so every browser call is cross-origin
        // and preflights. Preflight OPTIONS requests must be answered before
        // routing, which is why this is global middleware rather than route
        // middleware. The allowlist is built per-request from the active
        // storefront domains — see HandleStorefrontCors and config/cors.php.
        $middleware->prepend([
            HandleStorefrontCors::class,
            AddRequestContext::class,
            UseRequestUrl::class,
        ]);
        $middleware->append(SecureMcpTransport::class);

        // The app runs behind a TLS-terminating reverse proxy (Traefik) that
        // forwards to FrankenPHP as plain HTTP with X-Forwarded-* headers. The
        // :8080 listener is never published, so the immediate peer is always the
        // proxy — trust it, otherwise Laravel treats requests as insecure and
        // generates http:// asset URLs (mixed-content on the https page).
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->preventRequestForgery(except: [
            '/livewire/*',
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
