<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraFaq\Console\Commands\SeedCommand;
use Misaf\VendraFaq\FaqPlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FaqServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-faq')
            ->hasTranslations()
            ->hasMigrations([
                'create_faqs_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-faq');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-faq')) {
                return;
            }

            $panel->plugin(FaqPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('faq_categories', 'faqs');
        $this->app->make(TenantSeeders::class)->register('vendra-faq:seed', priority: 50);

        AboutCommand::add('Vendra Faq', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-faq')]);
    }
}
