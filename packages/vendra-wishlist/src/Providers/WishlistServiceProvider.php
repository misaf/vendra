<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraWishlist\Console\Commands\SeedCommand;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;
use Misaf\VendraWishlist\WishlistPlugin;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class WishlistServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-wishlist')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_wishlists_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-wishlist');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), WishlistPlugin::ID)) {
                return;
            }

            $panel->plugin(WishlistPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        /**
         * Stable aliases keep persisted morph columns decoupled from the model
         * FQCNs, so relocating a model class never orphans stored rows.
         */
        Relation::morphMap([
            'wishlist' => Wishlist::class,
            'wishlist_item' => WishlistItem::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('wishlists');
        $this->app->make(TenantSeeders::class)->register('vendra-wishlist:seed', priority: 61);

        AboutCommand::add('Vendra Wishlist', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-wishlist')]);
    }
}
