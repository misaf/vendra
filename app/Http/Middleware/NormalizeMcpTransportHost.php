<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class NormalizeMcpTransportHost
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Host', 'localhost');
        $request->headers->remove('Origin');
        $request->server->set('HTTP_HOST', 'localhost');
        $request->server->set('SERVER_NAME', 'localhost');
        $request->server->remove('HTTP_ORIGIN');

        return $next($request);
    }
}
