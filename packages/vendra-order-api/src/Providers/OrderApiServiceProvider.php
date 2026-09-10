<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\State\ProcessorInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraOrderApi\ApiResource\OrderResource;
use Misaf\VendraOrderApi\Policies\CustomerOrderPolicy;
use Misaf\VendraOrderApi\State\OrderLinksHandler;
use Misaf\VendraOrderApi\State\PlaceOrderProcessor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class OrderApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-order-api')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        Gate::policy(OrderResource::class, CustomerOrderPolicy::class);
        $this->app->tag(OrderLinksHandler::class, LinksHandlerInterface::class);
        $this->app->tag(PlaceOrderProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Order API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-order-api')]);
    }
}
