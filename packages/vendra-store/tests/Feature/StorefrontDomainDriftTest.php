<?php

declare(strict_types=1);

use Misaf\VendraStore\Actions\ReconcileStoreStorefrontAction;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionResult;

/*
 | A storefront is routed by a Host() rule baked into a container label, and
 | labels cannot be edited. A container answering a domain other than the
 | deployment's is drift, and convergence rebuilds it.
 */

describe('convergence', function (): void {
    it('reports drift when the container is routed on a domain the store has left', function (): void {
        $deployment = StorefrontDeployment::factory()->create(['domain' => 'new.test']);

        $observed = new StorefrontObservation(
            state: StorefrontRuntimeState::Running,
            image: $deployment->storefrontImage?->image,
            containerName: 'vendra-storefront-'.$deployment->slug,
            domain: 'old.test',
        );

        expect($observed->isServingDomainOtherThan($deployment->domain))->toBeTrue();
    });

    it('treats a container with no domain label as no evidence rather than drift', function (): void {
        $observed = new StorefrontObservation(
            state: StorefrontRuntimeState::Running,
            domain: null,
        );

        expect($observed->isServingDomainOtherThan('new.test'))->toBeFalse();
    });

    it('redeploys a healthy storefront that is still serving the old domain', function (): void {
        $store = Store::factory()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create([
            'domain' => 'new.test',
            'status' => StorefrontDeploymentStatus::Ready,
        ]);

        $provisioner = Mockery::mock(StorefrontProvisioner::class);
        $provisioner->shouldReceive('observe')->once()->andReturn(new StorefrontObservation(
            state: StorefrontRuntimeState::Running,
            image: $deployment->storefrontImage?->image,
            containerName: 'vendra-storefront-'.$deployment->slug,
            domain: 'old.test',
        ));
        $provisioner->shouldReceive('provision')->once()->andReturn(
            StorefrontProvisionResult::make(
                ready: true,
                reference: 'vendra-storefront-'.$deployment->slug,
                imageDigest: null,
            ),
        );

        app()->instance(StorefrontProvisioner::class, $provisioner);

        $outcome = resolve(ReconcileStoreStorefrontAction::class)->execute($deployment);

        expect($outcome)->toBe(StorefrontReconciliationOutcome::Redeployed);
    });
});
