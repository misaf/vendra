<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraOrder\Console\Commands\SeedCommand;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\Models\OrderLine;
use Misaf\VendraOrder\OrderPlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class OrderServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-order')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_orders_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-order');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), OrderPlugin::ID)) {
                return;
            }

            $panel->plugin(OrderPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        /**
         * Stable aliases keep persisted morph columns (order customers, and
         * orders referenced as a source elsewhere) decoupled from the model
         * FQCNs, so relocating a model class never orphans stored rows.
         */
        Relation::morphMap([
            'order' => Order::class,
            'order_line' => OrderLine::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('orders');
        $this->app->make(TenantSeeders::class)->register('vendra-order:seed', priority: 59);

        AboutCommand::add('Vendra Order', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-order')]);
    }
}
