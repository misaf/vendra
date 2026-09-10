<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraDeliveryApi\State\DeliveryScheduleProvider;
use Misaf\VendraDeliveryApi\State\DeliveryZoneLinksHandler;
use Misaf\VendraDeliveryApi\State\QuoteDeliveryProcessor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class DeliveryApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-delivery-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag(DeliveryZoneLinksHandler::class, LinksHandlerInterface::class);
        $this->app->tag(DeliveryScheduleProvider::class, ProviderInterface::class);
        $this->app->tag(QuoteDeliveryProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Delivery API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-delivery-api')]);
    }
}
