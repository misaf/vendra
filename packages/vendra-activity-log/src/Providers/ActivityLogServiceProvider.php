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
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasCommands(SeedCommand::class)
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
        // spatie/laravel-activitylog v5 dropped the `table_name` and
        // `database_connection` config keys; the table is fixed to `activity_log`
        // on the default connection.
        $this->app->make(TenantTableRegistry::class)->registerOnConnection(
            null,
            'activity_log',
        );
        $this->app->make(TenantSeeders::class)->register('vendra-activity-log:seed', priority: 85);

        AboutCommand::add('Vendra Activity Log', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-activity-log')]);

        $this->registerActivityLogListeners();
    }

    /**
     * Bind the activity logger to the wildcard Eloquent lifecycle events so any
     * model implementing ShouldLogActivity is logged without depending on this
     * package. Absence of this provider means no listeners and no logging.
     */
    private function registerActivityLogListeners(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
            Event::listen("eloquent.{$event}: *", LogModelActivity::class);
        }
    }
}
