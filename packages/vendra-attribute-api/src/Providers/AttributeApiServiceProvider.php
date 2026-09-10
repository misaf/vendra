<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAttributeApi\State\AttributeLinksHandler;
use Misaf\VendraAttributeApi\State\AttributeValueLinksHandler;
use Misaf\VendraAttributeApi\Support\AttributeApiServiceResolver;
use Misaf\VendraSupport\Contracts\AttributeApiResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AttributeApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-attribute-api');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AttributeApiResolver::class, AttributeApiServiceResolver::class);

        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag([
            AttributeLinksHandler::class,
            AttributeValueLinksHandler::class,
        ], LinksHandlerInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Attribute API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-attribute-api')]);
    }
}
