<?php

declare(strict_types=1);

namespace Misaf\VendraDeveloperLogins\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraDeveloperLogins\Support\DeveloperLoginsRegistrar;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class DeveloperLoginsServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-developer-logins')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-developer-logins');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-developer-logins')) {
                return;
            }

            $panel->plugin(DeveloperLoginsRegistrar::make());
        });
    }

    public function packageBooted(): void
    {
        AboutCommand::add('Vendra Developer Logins', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-developer-logins')]);
    }
}
