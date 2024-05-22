<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contract\Language as ContractLanguage;
use App\Models\Language\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureEloquentStrictMode();

        if ($this->app->environment('production')) {
            $this->configurePasswordDefaults();
        }
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
     * Configure password defaults.
     */
    private function configurePasswordDefaults(): void
    {
        Password::defaults(fn() => Password::min(8)->mixedCase());
    }

    /**
     * Log missing translation key.
     *
     * @param  string $key
     * @return string
     */
    private function logMissingTranslationKey(string $key): string
    {
        Log::info("Missing translation key [{$key}] detected.");
        return $key;
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
