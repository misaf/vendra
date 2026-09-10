<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraAttribute\AttributePlugin;
use Misaf\VendraAttribute\Console\Commands\SeedCommand;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraSupport\Capabilities\EloquentAttributeResolver;
use Misaf\VendraSupport\Contracts\AttributeResolver;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AttributeServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-attribute')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations(['create_attributes_table'])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-attribute');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            AttributeResolver::class,
            fn (): EloquentAttributeResolver => new EloquentAttributeResolver(Attribute::class, AttributeValue::class),
        );

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-attribute')) {
                return;
            }

            $panel->plugin(AttributePlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('attributes', 'attribute_values');
        $this->app->make(TenantSeeders::class)->register('vendra-attribute:seed', priority: 35);

        AboutCommand::add('Vendra Attribute', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-attribute')]);
    }
}
