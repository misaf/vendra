<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliateApi\Providers;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\State\ProcessorInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAffiliateApi\State\AffiliateLinksHandler;
use Misaf\VendraAffiliateApi\State\RecordReferralVisitProcessor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AffiliateApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-affiliate-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag(AffiliateLinksHandler::class, LinksHandlerInterface::class);
        $this->app->tag(RecordReferralVisitProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Affiliate API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-affiliate-api')]);
    }
}
