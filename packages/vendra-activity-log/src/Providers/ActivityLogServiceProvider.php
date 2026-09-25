<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Event;
use Misaf\VendraActivityLog\ActivityLogPlugin;
use Misaf\VendraActivityLog\Console\Commands\SeedCommand;
use Misaf\VendraActivityLog\Listeners\LogModelActivity;
use Misaf\VendraActivityLog\Listeners\LogSettingsActivity;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelSettings\Events\SavingSettings;

final class ActivityLogServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-activity-log')
            ->hasTranslations()
            ->hasMigrations([
                'create_activity_log_table',
            ])
            ->hasConsoleCommand(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-activity-log');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-activity-log')) {
                return;
            }

            $panel->plugin(ActivityLogPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        /*
        | `activity_log` is deliberately absent from the TenantTableRegistry: a
        | null tenant id is platform activity, so the `vendra-tenant:enable`
        | retrofit must never backfill those rows or force the column NOT NULL.
        */
        $this->app->make(TenantSeeders::class)->register(SeedCommand::class, priority: 85);

        AboutCommand::add('Vendra Activity Log', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-activity-log')]);

        $this->registerActivityLogListeners();
    }

    private function registerActivityLogListeners(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
            Event::listen("eloquent.{$event}: *", LogModelActivity::class);
        }

        Event::listen(SavingSettings::class, LogSettingsActivity::class);
    }
}
