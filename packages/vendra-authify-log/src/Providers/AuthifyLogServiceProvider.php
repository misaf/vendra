<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraAuthifyLog\AuthifyLogPlugin;
use Misaf\VendraAuthifyLog\Console\Commands\AuthifyLogChannelCommand;
use Misaf\VendraAuthifyLog\Console\Commands\SeedCommand;
use Misaf\VendraAuthifyLog\Models\AuthifyLog;
use Misaf\VendraAuthifyLog\Support\AuthifyLogUsers;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AuthifyLogServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-authify-log')
            ->hasTranslations()
            ->hasConfigFile()
            ->hasMigrations([
                'create_authify_logs_table',
            ])
            ->hasCommands(
                AuthifyLogChannelCommand::class,
                SeedCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-authify-log');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-authify-log')) {
                return;
            }

            $panel->plugin(AuthifyLogPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('authify_logs');
        $this->app->make(TenantSeeders::class)->register('vendra-authify-log:seed', priority: 90);

        AboutCommand::add('Vendra Authify Log', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-authify-log')]);

        $userModel = AuthifyLogUsers::model();
        $userModel::resolveRelationUsing('authifyLogs', fn (Model $user) => $user->hasMany(AuthifyLog::class));
    }
}
