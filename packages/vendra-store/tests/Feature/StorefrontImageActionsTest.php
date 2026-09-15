<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\DeleteStorefrontImageAction;
use Misaf\VendraStore\Actions\UpdateStorefrontImageAction;
use Misaf\VendraStore\Exceptions\StorefrontImageInUseException;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Models\StorefrontImage;

beforeEach(function (): void {
    Queue::fake();
});

it('refuses to delete a storefront image a deployment still uses', function (): void {
    $image = StorefrontDeployment::factory()->create()->storefrontImage;

    expect(fn () => resolve(DeleteStorefrontImageAction::class)->execute($image))->toThrow(StorefrontImageInUseException::class)
        ->and(StorefrontImage::query()->whereKey($image->getKey())->exists())->toBeTrue();
});

it('deletes an unused storefront image', function (): void {
    $image = StorefrontImage::factory()->create();

    resolve(DeleteStorefrontImageAction::class)->execute($image);

    expect(StorefrontImage::query()->whereKey($image->getKey())->exists())->toBeFalse();
});

it('deactivates a storefront image its deployments still use', function (): void {
    $deployment = StorefrontDeployment::factory()->create();

    resolve(UpdateStorefrontImageAction::class)->execute($deployment->storefrontImage, ['active' => false]);

    expect($deployment->refresh()->storefrontImage?->active)->toBeFalse();
});
