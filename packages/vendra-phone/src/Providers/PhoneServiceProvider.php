<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Providers;

use Misaf\VendraPhone\Console\Commands\SeedCommand;
use Misaf\VendraPhone\Filament\RelationManagers\PhoneNumbersRelationManager;
use Misaf\VendraPhone\Models\PhoneNumber;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class PhoneServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-phone')
            ->hasTranslations()
            ->hasCommands(SeedCommand::class)
            ->hasMigration('create_phone_numbers_table');
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('phone_numbers');
        $this->app->make(TenantSeeders::class)->register('vendra-phone:seed', priority: 23);

        UserProfile::resolveRelationUsing(
            'phoneNumbers',
            fn (UserProfile $profile) => $profile->hasMany(PhoneNumber::class),
        );

        $this->app->make(UserProfileRelationManagers::class)
            ->register(PhoneNumbersRelationManager::class, priority: 20);
    }
}
