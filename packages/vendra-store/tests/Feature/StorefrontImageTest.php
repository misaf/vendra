<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraStore\Models\StorefrontImage;
use Misaf\VendraStore\Support\StorefrontProvisionRequest;

beforeEach(function (): void {
    Queue::fake();
    Config::set('container.drivers.docker.host', '');
});

it('stores the selected image and derives provisioning data from its catalog entry', function (): void {
    $image = StorefrontImage::factory()->create([
        'image' => 'ghcr.io/misaf/storefront@sha256:abc123',
    ]);
    $store = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $store,
        'acme.test',
        [
            ...storefrontRequestData(),
            'storefront_image_id' => $image->id,
        ],
    );
    $request = StorefrontProvisionRequest::for($deployment);

    expect($deployment->storefront_image_id)->toBe($image->id)
        ->and($request->image)->toBe($image->image);
});
