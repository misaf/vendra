<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletterApi\Providers;

use ApiPlatform\State\ProcessorInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraNewsletterApi\State\SubscribeNewsletterProcessor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class NewsletterApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-newsletter-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag(SubscribeNewsletterProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Newsletter API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-newsletter-api')]);
    }
}
