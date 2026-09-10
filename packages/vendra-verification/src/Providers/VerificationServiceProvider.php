<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Providers;

use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;
use Misaf\VendraVerification\Console\Commands\SeedCommand;
use Misaf\VendraVerification\Filament\RelationManagers\VerificationsRelationManager;
use Misaf\VendraVerification\Models\Verification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class VerificationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-verification')
            ->hasTranslations()
            ->hasCommands(SeedCommand::class)
            ->hasMigration('create_verifications_table');
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('verifications');
        $this->app->make(TenantSeeders::class)->register('vendra-verification:seed', priority: 25);

        UserProfile::resolveRelationUsing(
            'verifications',
            fn (UserProfile $profile) => $profile->hasMany(Verification::class),
        );

        $this->app->make(UserProfileRelationManagers::class)
            ->register(VerificationsRelationManager::class, priority: 40);
    }
}
