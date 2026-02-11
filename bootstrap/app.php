<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Exceptions\ExceptionParser;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/livewire/*',
            '/webhooks/coinpayments',
            '/webhooks/resend',
            '*/oauth/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if ( ! class_exists(ExceptionParser::class) || ! class_exists(JsonApiException::class)) {
            return;
        }

        $exceptions->dontReport(JsonApiException::class);
        $exceptions->render(ExceptionParser::renderer());
    })->create();
