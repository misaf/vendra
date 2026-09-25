<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraMultimedia\Tests\Fixtures\Gallery;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;

beforeEach(function (): void {
    Storage::fake('public');
    Gallery::createTable();
});

/**
 * Bind entitlements that allow up to the given number of stored bytes.
 */
function capStorageAtBytes(int $bytes): void
{
    app()->instance(TenantEntitlements::class, new readonly class($bytes) implements TenantEntitlements
    {
        public function __construct(private int $bytes) {}

        public function allows(PlanFeature $feature, ?Model $tenant = null): bool
        {
            return true;
        }

        public function limit(PlanLimit $limit, ?Model $tenant = null): ?int
        {
            return 1;
        }

        public function assertAllows(PlanFeature $feature, ?Model $tenant = null): void {}

        public function canAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): bool
        {
            $usage = resolve(TenantUsageRegistry::class)->usage($limit, currentTestTenant()) ?? 0;

            return $usage + $amount <= $this->bytes;
        }

        public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void
        {
            throw_unless($this->canAdd($limit, $amount, $tenant), EntitlementExceededException::limitReached($limit, 1));
        }

        public function recordAdded(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void {}
    });
}

function addLimitedGalleryMedia(string $bytes): Multimedia
{
    return Gallery::query()->create()
        ->addMediaFromString($bytes)
        ->usingFileName('photo.jpg')
        ->toMediaCollection(diskName: 'public');
}

it('counts only the given store media bytes toward the plan limit', function (): void {
    $store = makeCurrentTestTenant();
    addLimitedGalleryMedia(str_repeat('a', 10));
    addLimitedGalleryMedia(str_repeat('b', 5));

    makeCurrentTestTenant();
    addLimitedGalleryMedia(str_repeat('c', 100));

    expect(resolve(TenantUsageRegistry::class)->usage(PlanLimit::StorageMegabytesPerStore, $store))->toBe(15);
});

it('refuses to store media past the plan storage limit', function (): void {
    makeCurrentTestTenant();
    capStorageAtBytes(12);
    addLimitedGalleryMedia(str_repeat('a', 10));

    expect(fn () => addLimitedGalleryMedia(str_repeat('b', 5)))
        ->toThrow(EntitlementExceededException::class, 'Storage per store (MB)')
        ->and(Multimedia::query()->count())->toBe(1);
});

it('fails upload validation for a file past the plan storage limit', function (): void {
    makeCurrentTestTenant();
    capStorageAtBytes(2 * 1024);

    $validate = fn (int $kilobytes): bool => Validator::make(
        ['image' => UploadedFile::fake()->create('photo.jpg', $kilobytes)],
        ['image' => [ModelImageUpload::storageLimitRule()]],
    )->passes();

    expect($validate(1))->toBeTrue()
        ->and($validate(3))->toBeFalse();
});

it('warns on the upload field once the store storage is full', function (): void {
    makeCurrentTestTenant();
    capStorageAtBytes(10);

    expect(ModelImageUpload::storageFullMessage(resolve(TenantEntitlements::class)))->toBeNull();

    addLimitedGalleryMedia(str_repeat('a', 10));

    expect(ModelImageUpload::storageFullMessage(resolve(TenantEntitlements::class)))->toContain(PlanLimit::StorageMegabytesPerStore->getLabel());
});
