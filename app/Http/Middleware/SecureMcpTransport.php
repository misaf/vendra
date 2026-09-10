<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Misaf\VendraLocalization\Http\Middleware\SetLocale;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

final readonly class SecureMcpTransport
{
    public function __construct(
        private Router $router,
        private Pipeline $pipeline,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('mcp')) {
            return $next($request);
        }

        $middleware = $this->router->resolveMiddleware([
            ResolveApiTenant::class,
            NeedsTenant::class,
            SetLocale::class,
            'auth:sanctum',
            'throttle:mcp',
            NormalizeMcpTransportHost::class,
        ]);

        $response = $this->pipeline
            ->send($request)
            ->through($middleware)
            ->then(fn (Request $request): Response => $next($request));

        if (! $response instanceof Response) {
            throw new UnexpectedValueException('The secure MCP transport pipeline must return a response.');
        }

        return $response;
    }
}
