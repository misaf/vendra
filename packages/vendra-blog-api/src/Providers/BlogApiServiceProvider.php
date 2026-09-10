<?php

declare(strict_types=1);

namespace Misaf\VendraBlogApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraBlogApi\State\BlogPostCategoryLinksHandler;
use Misaf\VendraBlogApi\State\BlogPostLinksHandler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BlogApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-blog-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag([
            BlogPostLinksHandler::class,
            BlogPostCategoryLinksHandler::class,
        ], LinksHandlerInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Blog API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-blog-api')]);
    }
}
