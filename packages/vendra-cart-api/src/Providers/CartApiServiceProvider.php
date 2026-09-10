<?php

declare(strict_types=1);

namespace Misaf\VendraCartApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraCartApi\ApiResource\CartResource;
use Misaf\VendraCartApi\Policies\ShoppingCartPolicy;
use Misaf\VendraCartApi\State\CartLinksHandler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CartApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-cart-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        Gate::policy(CartResource::class, ShoppingCartPolicy::class);
        $this->app->tag(CartLinksHandler::class, LinksHandlerInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Cart API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-cart-api')]);
    }
}
