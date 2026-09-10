<?php

declare(strict_types=1);

use App\Http\Middleware\NormalizeMcpTransportHost;
use App\Http\Middleware\ResolveApiTenant;
use App\Http\Middleware\SecureMcpTransport;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Misaf\VendraLocalization\Http\Middleware\SetLocale;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Symfony\Component\HttpFoundation\Response;

it('bypasses the secure transport pipeline for other paths', function (): void {
    $expectedResponse = new Response;
    $middleware = new SecureMcpTransport(
        Mockery::mock(Router::class),
        app(Pipeline::class),
    );

    $response = $middleware->handle(
        Request::create('/health'),
        fn (Request $request): Response => $expectedResponse,
    );

    expect($response)->toBe($expectedResponse);
});

it('passes mcp requests through the secure transport pipeline', function (): void {
    $router = Mockery::mock(Router::class);
    $router->shouldReceive('resolveMiddleware')
        ->once()
        ->with([
            ResolveApiTenant::class,
            NeedsTenant::class,
            SetLocale::class,
            'auth:sanctum',
            'throttle:mcp',
            NormalizeMcpTransportHost::class,
        ])
        ->andReturn([
            function (Request $request, Closure $next): Response {
                $request->attributes->set('secure_transport_applied', true);

                return $next($request);
            },
        ]);

    $expectedResponse = new Response;
    $middleware = new SecureMcpTransport($router, app(Pipeline::class));

    $response = $middleware->handle(
        Request::create('/mcp'),
        function (Request $request) use ($expectedResponse): Response {
            expect($request->attributes->get('secure_transport_applied'))->toBeTrue();

            return $expectedResponse;
        },
    );

    expect($response)->toBe($expectedResponse);
});

it('rejects invalid responses from the secure transport pipeline', function (): void {
    $router = Mockery::mock(Router::class);
    $router->shouldReceive('resolveMiddleware')
        ->once()
        ->andReturn([
            fn (Request $request, Closure $next): string => 'invalid response',
        ]);

    $middleware = new SecureMcpTransport($router, app(Pipeline::class));

    expect(fn () => $middleware->handle(
        Request::create('/mcp'),
        fn (Request $request): Response => new Response,
    ))->toThrow(
        UnexpectedValueException::class,
        'The secure MCP transport pipeline must return a response.',
    );
});
