<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Misaf\VendraDocument\Models\Document;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraUserProfile\Models\UserProfile;

it('persists documents against the installed user profile', function (): void {
    makeCurrentTestTenant();
    $profile = UserProfile::factory()->forUser(createTestUser())->create();

    $document = Document::factory()->create([
        'user_profile_id' => $profile->id,
        'issuing_country_code' => 'IR',
        'metadata' => ['authority' => 'Civil Registry'],
    ]);

    expect($profile->documents())->toBeInstanceOf(HasMany::class)
        ->and($profile->documents()->sole()->is($document))->toBeTrue()
        ->and($document->tenant_id)->toBe(1);
});

it('uses scalar country-aware columns and structured metadata', function (): void {
    expect(Schema::getColumnType('documents', 'issuing_country_code'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('documents', 'metadata'))->toBeIn(['json', 'text']);
});

it('stores private document files through Vendra Multimedia', function (): void {
    Storage::fake('local');
    makeCurrentTestTenant();
    $profile = UserProfile::factory()->forUser(createTestUser())->create();
    $document = Document::factory()->create(['user_profile_id' => $profile->id]);

    $media = $document
        ->addMediaFromString('private document')
        ->usingFileName('passport.pdf')
        ->toMediaCollection(Document::MEDIA_COLLECTION);

    expect($media)->toBeInstanceOf(Multimedia::class)
        ->and($media->disk)->toBe('local')
        ->and($media->tenant_id)->toBe(1)
        ->and($document->getMedia(Document::MEDIA_COLLECTION))->toHaveCount(1)
        ->and(Schema::hasColumns('documents', ['disk', 'path']))->toBeFalse();

    Storage::disk('local')->assertExists($media->getPathRelativeToRoot());
});
