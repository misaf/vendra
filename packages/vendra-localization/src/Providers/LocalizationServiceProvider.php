<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Context\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Http\Middleware\SetLocale;
use Misaf\VendraLocalization\LocaleManager;
use Misaf\VendraLocalization\Resolvers\ChainLocaleResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LocalizationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-localization')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->bind(LocaleResolver::class, fn (Application $app): LocaleResolver => ChainLocaleResolver::fromSources(
            $app,
            Config::array('vendra-localization.resolvers'),
        ));
    }

    public function packageBooted(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('vendra.locale', SetLocale::class);

        $localeManager = $this->app->make(LocaleManager::class);

        Context::dehydrating(static function (Repository $context): void {
            $context->addHidden(LocaleManager::CONTEXT_KEY, App::currentLocale());
        });
        Context::hydrated(static function (Repository $context) use ($localeManager): void {
            $locale = $context->getHidden(LocaleManager::CONTEXT_KEY);

            if (is_string($locale) && $locale !== '') {
                $localeManager->apply($locale);
            }
        });
    }
}
