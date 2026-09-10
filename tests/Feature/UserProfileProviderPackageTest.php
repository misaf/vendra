<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Misaf\VendraAddress\Filament\RelationManagers\AddressesRelationManager;
use Misaf\VendraDocument\Filament\RelationManagers\DocumentsRelationManager;
use Misaf\VendraPhone\Filament\RelationManagers\PhoneNumbersRelationManager;
use Misaf\VendraUserProfile\Support\UserProfileRelationManagers;
use Misaf\VendraVerification\Filament\RelationManagers\VerificationsRelationManager;

it('registers installed user profile providers in deterministic order', function (): void {
    expect(resolve(UserProfileRelationManagers::class)->all())->toBe([
        AddressesRelationManager::class,
        PhoneNumbersRelationManager::class,
        DocumentsRelationManager::class,
        VerificationsRelationManager::class,
    ]);
});

it('keeps user profile providers independently selectable', function (): void {
    $rootComposer = json_decode(File::get(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $providers = [
        'vendra-address',
        'vendra-phone',
        'vendra-document',
        'vendra-verification',
    ];

    foreach ($providers as $provider) {
        $composer = json_decode(
            File::get(base_path("packages/{$provider}/composer.json")),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect(Arr::get($rootComposer, 'require'))->toHaveKey("misaf/{$provider}")
            ->and(Arr::get($composer, 'require'))->toHaveKey('misaf/vendra-user-profile');

        foreach (array_diff($providers, [$provider]) as $otherProvider) {
            expect(Arr::get($composer, 'require'))->not->toHaveKey("misaf/{$otherProvider}");
        }
    }

    expect(Arr::get($rootComposer, 'require'))->not->toHaveKey('ysfkaya/filament-phone-input')
        ->and(Arr::get(json_decode(
            File::get(base_path('packages/vendra-phone/composer.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ), 'require'))->toHaveKey('ysfkaya/filament-phone-input')
        ->and(Arr::get(json_decode(
            File::get(base_path('packages/vendra-document/composer.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ), 'require'))
        ->toHaveKey('misaf/vendra-multimedia')
        ->not->toHaveKey('filament/spatie-laravel-media-library-plugin');
});

it('enforces one-way provider boundaries', function (): void {
    $providers = [
        'vendra-address' => 'Misaf\\VendraAddress',
        'vendra-phone' => 'Misaf\\VendraPhone',
        'vendra-document' => 'Misaf\\VendraDocument',
        'vendra-verification' => 'Misaf\\VendraVerification',
    ];

    $userProfileSource = collect(File::allFiles(base_path('packages/vendra-user-profile/src')))
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");

    foreach ($providers as $provider => $namespace) {
        expect($userProfileSource)->not->toContain($namespace);

        $providerSource = collect(File::allFiles(base_path("packages/{$provider}/src")))
            ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
            ->implode("\n");

        expect($providerSource)
            ->not->toContain('Misaf\\VendraTenant');

        foreach (array_diff($providers, [$provider => $namespace]) as $otherNamespace) {
            expect($providerSource)->not->toContain($otherNamespace);
        }
    }
});

it('uses the international phone input contract', function (): void {
    $relationManager = File::get(base_path(
        'packages/vendra-phone/src/Filament/RelationManagers/PhoneNumbersRelationManager.php',
    ));

    expect($relationManager)
        ->toContain("PhoneInput::make('number')")
        ->toContain("->countryStatePath('country_code')")
        ->toContain('PhoneInputNumberType::E164')
        ->toContain("PhoneColumn::make('number')");
});

it('stores document files through Vendra Multimedia', function (): void {
    $model = File::get(base_path('packages/vendra-document/src/Models/Document.php'));
    $relationManager = File::get(base_path(
        'packages/vendra-document/src/Filament/RelationManagers/DocumentsRelationManager.php',
    ));
    $migration = File::get(base_path(
        'packages/vendra-document/database/migrations/create_documents_table.php.stub',
    ));

    expect($model)
        ->toContain('implements HasMedia')
        ->toContain('use InteractsWithMedia')
        ->toContain("MEDIA_COLLECTION = 'documents'")
        ->and($relationManager)
        ->toContain("SpatieMediaLibraryFileUpload::make('file')")
        ->toContain('->visibility(\'private\')')
        ->and($migration)
        ->not->toContain("\$table->string('disk')")
        ->not->toContain("\$table->string('path')");
});
