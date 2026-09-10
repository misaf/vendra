<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Uri;
use Symfony\Component\HttpFoundation\Response;

final class UseRequestUrl
{
    private const string ORIGINAL_ASSET_URL = self::class.'.original_asset_url';

    private const string ORIGINAL_URL = self::class.'.original_url';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $originalUrl = Config::string('app.url');
        $appHost = Uri::of($originalUrl)->host();

        if (! in_array($request->getHost(), ['console.'.$appHost, 'reseller.'.$appHost], true)) {
            return $next($request);
        }

        $configuredAssetUrl = Config::get('app.asset_url');
        $originalAssetUrl = is_string($configuredAssetUrl) ? $configuredAssetUrl : null;

        // The app is https-only (see URL::forceScheme('https')) and runs behind a
        // TLS-terminating proxy that forwards over plain HTTP. This middleware is
        // prepended — it runs before TrustProxies — so $request->getScheme() may
        // still read http here. Pin the origin to https so central-panel assets
        // and URLs never fall back to http (mixed content on the https page).
        $requestUrl = 'https://'.$request->getHttpHost();

        $request->attributes->set(self::ORIGINAL_URL, $originalUrl);
        $request->attributes->set(self::ORIGINAL_ASSET_URL, $originalAssetUrl);

        Config::set('app.url', $requestUrl);
        Config::set('app.asset_url', $requestUrl);
        URL::useOrigin($requestUrl);
        URL::useAssetOrigin($requestUrl);

        return $next($request);
    }

    public function terminate(Request $request): void
    {
        $originalUrl = $request->attributes->get(self::ORIGINAL_URL);
        $originalAssetUrl = $request->attributes->get(self::ORIGINAL_ASSET_URL);

        if (! is_string($originalUrl)) {
            return;
        }

        Config::set('app.url', $originalUrl);
        Config::set('app.asset_url', is_string($originalAssetUrl) ? $originalAssetUrl : null);
        URL::useOrigin($originalUrl);
        URL::useAssetOrigin(is_string($originalAssetUrl) ? $originalAssetUrl : null);
    }
}
