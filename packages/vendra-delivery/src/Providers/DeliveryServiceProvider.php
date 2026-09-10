<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraDelivery\Console\Commands\SeedCommand;
use Misaf\VendraDelivery\DeliveryPlugin;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class DeliveryServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-delivery')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_deliveries_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-delivery');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), DeliveryPlugin::ID)) {
                return;
            }

            $panel->plugin(DeliveryPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        /**
         * Stable aliases keep persisted morph columns decoupled from the model
         * FQCNs, so relocating a model class never orphans stored rows.
         */
        Relation::morphMap([
            'delivery' => Delivery::class,
            'delivery_slot' => DeliverySlot::class,
            'delivery_zone' => DeliveryZone::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('delivery_zones', 'delivery_slots', 'deliveries');
        $this->app->make(TenantSeeders::class)->register('vendra-delivery:seed', priority: 60);

        AboutCommand::add('Vendra Delivery', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-delivery')]);
    }
}
