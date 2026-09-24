<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

final class UseRequestUrl
{
    private const string ORIGINAL_URL = self::class.'.original_url';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $centralHost = Config::string('vendra-tenant.central_host');

        if (! in_array($request->getHost(), ['console.'.$centralHost, 'reseller.'.$centralHost], true)) {
            return $next($request);
        }

        // Force https, since this runs before TrustProxies and may still see http.
        $requestUrl = 'https://'.$request->getHttpHost();

        $request->attributes->set(self::ORIGINAL_URL, Config::string('app.url'));

        Config::set('app.url', $requestUrl);
        URL::useOrigin($requestUrl);

        return $next($request);
    }

    public function terminate(Request $request): void
    {
        $originalUrl = $request->attributes->get(self::ORIGINAL_URL);

        if (! is_string($originalUrl)) {
            return;
        }

        Config::set('app.url', $originalUrl);
        URL::useOrigin($originalUrl);
    }
}
