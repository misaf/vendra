<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Laravel\Pennant\Feature;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUserProfile\Console\Commands\SeedCommand;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;
use Misaf\VendraUserProfile\UserProfilePlugin;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class UserProfileServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-user-profile')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_user_profiles_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-user-profile');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(UserProfileRelationManagers::class);

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-user-profile')) {
                return;
            }

            $panel->plugin(UserProfilePlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('user_profiles');
        $this->app->make(TenantSeeders::class)->register('vendra-user-profile:seed', priority: 21);

        AboutCommand::add('Vendra User Profile', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-user-profile')]);

        Gate::after(function (Authenticatable $user): ?true {
            if (! $user instanceof User) {
                return null;
            }

            return $user->hasRole(Config::string('vendra-permission.admin_role')) ? true : null;
        });

        $this->registerUserProfileRelationship();
        $this->discoverPackageFeatures();
    }

    private function registerUserProfileRelationship(): void
    {
        User::resolveRelationUsing('profiles', fn (User $user) => $user->hasMany(UserProfile::class));
        User::resolveRelationUsing('userProfiles', fn (User $user) => $user->hasMany(UserProfile::class));
    }

    private function discoverPackageFeatures(): void
    {
        $featureNamespace = 'Misaf\\VendraUserProfile\\Features';
        $featurePath = dirname(__DIR__).'/Features';

        if (Config::boolean('vendra-user-profile.features_discover', false) && is_dir($featurePath)) {
            Feature::discover($featureNamespace, $featurePath);
        }
    }
}
