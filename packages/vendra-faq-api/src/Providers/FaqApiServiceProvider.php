<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraFaqApi\State\FaqCategoryLinksHandler;
use Misaf\VendraFaqApi\State\FaqLinksHandler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FaqApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-faq-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag([
            FaqLinksHandler::class,
            FaqCategoryLinksHandler::class,
        ], LinksHandlerInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Faq API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-faq-api')]);
    }
}
