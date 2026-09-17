<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
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

it("keeps the deployment's own identity over the stored configuration", function (): void {
    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        createTestTenant(),
        'acme.test',
        [
            ...storefrontRequestData(),
            'storefront_image_id' => StorefrontImage::factory()->create()->id,
        ],
    );
    $deployment->forceFill(['configuration' => ['siteUrl' => 'https://elsewhere.test', 'domain' => 'elsewhere.test']])->save();

    $request = StorefrontProvisionRequest::for($deployment);

    expect(Arr::get($request->configuration, 'siteUrl'))->toBe('https://acme.test')
        ->and(Arr::get($request->configuration, 'domain'))->toBe('acme.test');
});
