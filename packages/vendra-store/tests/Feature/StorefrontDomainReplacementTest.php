<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Misaf\VendraStore\Actions\ReconcileStoreStorefrontAction;
use Misaf\VendraStore\Actions\ReplaceStoreDomainAction;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Enums\StorefrontRuntimeState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontObservation;
use Misaf\VendraStore\Support\StorefrontProvisionResult;

/*
 | A storefront is routed by a Host() rule baked into a container label, and
 | labels cannot be edited. Moving a store to a new domain therefore has to reach
 | the container, or the storefront answers the old address and nothing answers
 | the new one.
 */

it('moves the storefront deployment to the new domain', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'old.test']);

    resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($deployment->fresh()?->domain)->toBe('new.test');
});

it('forces a redeploy so the container is rebuilt with the new routing label', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'old.test']);

    resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
    );
});

it('rejects a domain another store already runs its storefront on', function (): void {
    Queue::fake();

    $other = Store::factory()->create();
    StorefrontDeployment::factory()->for($other)->create(['domain' => 'taken.test']);

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);

    // Without the rule this reaches the database and comes back as a query
    // error mid-transaction rather than a message on the field.
    expect(fn () => resolve(ReplaceStoreDomainAction::class)->execute($store, 'taken.test'))
        ->toThrow(ValidationException::class);
});

it('still records the new domain when no container runtime is configured', function (): void {
    Queue::fake();
    config()->set('container.drivers.docker.host', '');

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['domain' => 'old.test']);

    resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($deployment->fresh()?->domain)->toBe('new.test');

    // Nothing to dispatch to; convergence rebuilds it once the estate is up.
    Queue::assertNotPushed(ProvisionStorefrontJob::class);
});

it('leaves a store with no storefront alone', function (): void {
    Queue::fake();

    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);

    $replaced = resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($replaced->name)->toBe('new.test');

    Queue::assertNotPushed(ProvisionStorefrontJob::class);
});

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
