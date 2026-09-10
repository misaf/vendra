<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraProductApi\State\ProductCategoryLinksHandler;
use Misaf\VendraProductApi\State\ProductLinksHandler;
use Misaf\VendraProductApi\State\ProductPriceLinksHandler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ProductApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-product-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag([
            ProductLinksHandler::class,
            ProductCategoryLinksHandler::class,
            ProductPriceLinksHandler::class,
        ], LinksHandlerInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Product API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-product-api')]);
    }
}
