<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraCurrency\Console\Commands\SeedCommand;
use Misaf\VendraCurrency\CurrencyPlugin;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Capabilities\EloquentCurrencyResolver;
use Misaf\VendraSupport\Contracts\CurrencyResolver;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CurrencyServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-currency')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_currencies_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-currency');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            CurrencyResolver::class,
            fn (): EloquentCurrencyResolver => new EloquentCurrencyResolver(Currency::class),
        );

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-currency')) {
                return;
            }

            $panel->plugin(CurrencyPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('currencies');
        $this->app->make(TenantSeeders::class)->register('vendra-currency:seed', priority: 30);

        AboutCommand::add('Vendra Currency', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-currency')]);
    }
}
