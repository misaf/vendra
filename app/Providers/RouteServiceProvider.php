<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;

final class RouteServiceProvider extends ServiceProvider
{
    use LoadsTranslatedCachedRoutes;

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureRoutes();
    }

    /**
     * Configure API routes.
     */
    private function configureApiRoutes(): void
    {
        Route::middleware(['api', 'tenant', 'localizationRedirect', 'localeCookieRedirect'])
            ->prefix(LaravelLocalization::setLocale() . '/api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure rate limiting for the API.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): void {
            Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure application routes.
     */
    private function configureRoutes(): void
    {
        $this->configureApiRoutes();
        $this->configureWebRoutes();
    }

    /**
     * Configure web routes.
     */
    private function configureWebRoutes(): void
    {
        Route::middleware(['web', 'tenant', 'localizationRedirect', 'localeCookieRedirect'])
            ->prefix(LaravelLocalization::setLocale())
            ->group(base_path('routes/web.php'));
    }
}
