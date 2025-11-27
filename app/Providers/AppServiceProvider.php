<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contract\Language as ContractLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureEloquentStrictMode();

        URL::forceScheme('https');

        $this->app['request']->server->set('HTTPS', 'on');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // $this->app->bind(ContractLanguage::class, Language::class);
    }

    /**
     * Configure Eloquent strict mode.
     */
    private function configureEloquentStrictMode(): void
    {
        Model::shouldBeStrict($this->app->environment('production'));
    }

    /**
     * Log missing translation key.
     *
     * @param string $key
     */
    private function logMissingTranslationKey(string $key): void
    {
        Log::info("Missing translation key [{$key}] detected.");
    }

    /**
     * Register database query logger.
     */
    private function registerDatabaseQueryLogger(): void
    {
        DB::listen(fn($query) => Log::info($query->sql, $query->bindings));
    }

    /**
     * Register missing translation handler.
     */
    private function registerMissingTranslationHandler(): void
    {
        Lang::handleMissingKeysUsing(fn(string $key, array $replacements, string $locale) => $this->logMissingTranslationKey($key));
    }
}
