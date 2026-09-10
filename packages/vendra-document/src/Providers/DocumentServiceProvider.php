<?php

declare(strict_types=1);

namespace Misaf\VendraDocument\Providers;

use Misaf\VendraDocument\Console\Commands\SeedCommand;
use Misaf\VendraDocument\Filament\RelationManagers\DocumentsRelationManager;
use Misaf\VendraDocument\Models\Document;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class DocumentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-document')
            ->hasTranslations()
            ->hasCommands(SeedCommand::class)
            ->hasMigration('create_documents_table');
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('documents');
        $this->app->make(TenantSeeders::class)->register('vendra-document:seed', priority: 24);

        UserProfile::resolveRelationUsing(
            'documents',
            fn (UserProfile $profile) => $profile->hasMany(Document::class),
        );

        $this->app->make(UserProfileRelationManagers::class)
            ->register(DocumentsRelationManager::class, priority: 30);
    }
}
