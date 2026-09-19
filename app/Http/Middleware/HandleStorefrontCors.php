<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Fruitcake\Cors\CorsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Misaf\VendraStore\Support\StorefrontOrigins;

/**
 * Set here because the parent reapplies `config('cors')` on every request.
 */
final class HandleStorefrontCors extends HandleCors
{
    public function __construct(
        Container $container,
        CorsService $cors,
        private readonly StorefrontOrigins $origins,
    ) {
        parent::__construct($container, $cors);
    }

    /**
     * @param  Closure(Request): mixed  $next
     */
    public function handle($request, Closure $next)
    {
        // Only look up origins for paths CORS covers, such as `api/*`.
        if ($this->hasMatchingPath($request)) {
            Config::set('cors.allowed_origins', $this->origins->all());
        }

        return parent::handle($request, $next);
    }
}
