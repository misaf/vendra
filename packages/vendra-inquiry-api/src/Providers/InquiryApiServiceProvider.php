<?php

declare(strict_types=1);

namespace Misaf\VendraInquiryApi\Providers;

use ApiPlatform\State\ProcessorInterface;
use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraInquiryApi\State\SubmitInquiryProcessor;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class InquiryApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('vendra-inquiry-api');
    }

    public function packageRegistered(): void
    {
        Config::set('api-platform.resources', [
            ...Config::array('api-platform.resources', []),
            dirname(__DIR__).'/ApiResource',
        ]);

        $this->app->tag(SubmitInquiryProcessor::class, ProcessorInterface::class);
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Inquiry API', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-inquiry-api')]);
    }
}
