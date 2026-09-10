<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraCustomPage\Console\Commands\SeedCommand;
use Misaf\VendraCustomPage\CustomPagePlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CustomPageServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-custom-page')
            ->hasTranslations()
            ->hasMigrations([
                'create_custom_pages_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-custom-page');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-custom-page')) {
                return;
            }

            $panel->plugin(CustomPagePlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('custom_page_categories', 'custom_pages');
        $this->app->make(TenantSeeders::class)->register('vendra-custom-page:seed', priority: 60);

        AboutCommand::add('Vendra Custom Page', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-custom-page')]);
    }
}
