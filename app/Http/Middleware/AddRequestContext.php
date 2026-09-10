<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Context\RequestJobContext;
use Symfony\Component\HttpFoundation\Response;

final class AddRequestContext
{
    public const string REQUEST_ID_HEADER = 'X-Request-ID';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);

        new RequestJobContext(traceId: $requestId)->add();

        $response = $next($request);
        $response->headers->set(self::REQUEST_ID_HEADER, $requestId);

        return $response;
    }

    private function requestId(Request $request): string
    {
        $requestId = $request->headers->get(self::REQUEST_ID_HEADER);

        if (is_string($requestId) && Str::isUuid($requestId)) {
            return Str::lower($requestId);
        }

        return (string) Str::uuid();
    }
}
