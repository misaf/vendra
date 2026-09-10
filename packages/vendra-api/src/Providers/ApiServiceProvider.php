<?php

declare(strict_types=1);

namespace Misaf\VendraApi\Providers;

use ApiPlatform\Laravel\Eloquent\Filter\FilterInterface;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraApi\Eloquent\Filter\LocalizedEqualsFilter;
use Misaf\VendraApi\Eloquent\Filter\LocalizedSearchFilter;
use Misaf\VendraApi\Eloquent\Filter\RandomOrderFilter;
use Misaf\VendraApi\OpenApi\TagFilteredOpenApiFactory;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-api');
    }

    public function packageRegistered(): void
    {
        $this->app->tag([
            LocalizedEqualsFilter::class,
            LocalizedSearchFilter::class,
            RandomOrderFilter::class,
        ], FilterInterface::class);

        $this->app->tag(EloquentResourceProvider::class, ProviderInterface::class);

        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);
    }

    public function packageBooted(): void
    {
        $this->app->extend(
            OpenApiFactoryInterface::class,
            fn (OpenApiFactoryInterface $factory): OpenApiFactoryInterface => new TagFilteredOpenApiFactory($factory),
        );

        if (! $this->app->runningInConsole()) {
            return;
        }

        AboutCommand::add('Vendra API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-api')]);
    }
}
