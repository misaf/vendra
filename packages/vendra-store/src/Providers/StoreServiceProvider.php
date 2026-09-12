<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraStore\Console\Commands\ReconcileStorefrontDeploymentsCommand;
use Misaf\VendraStore\Console\Commands\RedeployStorefrontsCommand;
use Misaf\VendraStore\Console\Commands\RetryFailedStorefrontDeploymentsCommand;
use Misaf\VendraStore\Console\Commands\StorefrontDeploymentStatusCommand;
use Misaf\VendraStore\Console\Commands\StorefrontLifecycleCommand;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Observers\StoreDomainObserver;
use Misaf\VendraStore\Services\ContainerStorefrontProvisioner;
use Misaf\VendraStore\Services\StoreDomainFinder;
use Misaf\VendraStore\Support\NullStoreResellerResolver;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;
use Misaf\VendraStore\Support\StorefrontSettings;
use Misaf\VendraTenant\Contracts\HostTenantFinder;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class StoreServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-store')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_stores_table',
                'create_storefront_images_table',
                'create_storefront_deployments_table',
            ])
            ->hasCommands(
                ReconcileStorefrontDeploymentsCommand::class,
                RedeployStorefrontsCommand::class,
                RetryFailedStorefrontDeploymentsCommand::class,
                StorefrontDeploymentStatusCommand::class,
                StorefrontLifecycleCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->publishConfigFile()->askToStarRepoOnGitHub('misaf/vendra-store');
            });
    }

    public function packageRegistered(): void
    {
        /*
         | Bound rather than shared, so a configuration change — a test setting
         | an image, an administrator reloading config — is picked up on the next
         | resolve rather than frozen at first use.
         */
        $this->app->bind(StorefrontSettings::class, static fn (): StorefrontSettings => StorefrontSettings::fromConfig());
        $this->app->bind(StorefrontRuntimeConfiguration::class, static fn (): StorefrontRuntimeConfiguration => StorefrontRuntimeConfiguration::fromConfig());

        // One container per storefront, through the configured Laravel Docker Engine driver.
        $this->app->bind(StorefrontProvisioner::class, ContainerStorefrontProvisioner::class);

        /*
         | Stores own their domains, so this package supplies the adapter behind
         | the tenancy engine's host-resolution port. The engine keeps knowing
         | nothing about store domains.
         */
        $this->app->bind(HostTenantFinder::class, StoreDomainFinder::class);

        /*
         | The billing reseller is optional: misaf/vendra-reseller binds its own
         | resolver, and without it a store simply has none. `bindIf`
         | keeps this a default rather than a race with provider order.
         */
        $this->app->bindIf(StoreResellerResolver::class, NullStoreResellerResolver::class);
    }

    public function packageBooted(): void
    {
        StoreDomain::observe(StoreDomainObserver::class);

        AboutCommand::add('Vendra Store', fn (): array => [
            'Version' => InstalledVersions::getPrettyVersion('misaf/vendra-store'),
        ]);
    }
}
