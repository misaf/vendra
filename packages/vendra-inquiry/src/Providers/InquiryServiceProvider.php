<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraInquiry\Console\Commands\SeedCommand;
use Misaf\VendraInquiry\InquiryPlugin;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class InquiryServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-inquiry')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_inquiries_table',
            ])
            ->hasConsoleCommand(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-inquiry');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), InquiryPlugin::ID)) {
                return;
            }

            $panel->plugin(InquiryPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        // A stable alias, so moving the model class never orphans stored morph rows.
        Relation::morphMap([
            'inquiry' => Inquiry::class,
        ]);

        $this->app->make(TenantTableRegistry::class)->register('inquiries');
        $this->app->make(TenantSeeders::class)->register(SeedCommand::class, priority: 62);

        AboutCommand::add('Vendra Inquiry', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-inquiry')]);
    }
}
