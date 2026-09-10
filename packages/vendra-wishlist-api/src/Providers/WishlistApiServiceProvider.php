<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\State\ProcessorInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraWishlistApi\ApiResource\WishlistResource;
use Misaf\VendraWishlistApi\Policies\CustomerWishlistPolicy;
use Misaf\VendraWishlistApi\State\ForgetWishlistItemProcessor;
use Misaf\VendraWishlistApi\State\SaveWishlistItemProcessor;
use Misaf\VendraWishlistApi\State\WishlistLinksHandler;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class WishlistApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-wishlist-api')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        Gate::policy(WishlistResource::class, CustomerWishlistPolicy::class);
        $this->app->tag(WishlistLinksHandler::class, LinksHandlerInterface::class);
        $this->app->tag(SaveWishlistItemProcessor::class, ProcessorInterface::class);
        $this->app->tag(ForgetWishlistItemProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Wishlist API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-wishlist-api')]);
    }
}
