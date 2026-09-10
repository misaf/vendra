<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\DestroyStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontReference;

/*
 | Deleting a store has to reach its container, or the platform leaks a
 | storefront that keeps serving the customer's domain. These cover the cascade
 | at the observer, which is the one point every delete path passes through.
 */

describe('soft delete', function (): void {
    it('records the storefront as stopped and queues convergence', function (): void {
        Queue::fake();

        $store = Store::factory()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create([
            'desired_state' => StorefrontDesiredState::Running,
        ]);

        $store->delete();

        expect($deployment->fresh()?->desired_state)->toBe(StorefrontDesiredState::Stopped);

        Queue::assertPushed(
            ReconcileStorefrontJob::class,
            fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id,
        );
        Queue::assertNotPushed(DestroyStorefrontJob::class);
    });

    it('keeps the deployment row so restoring the store is a start, not a redeploy', function (): void {
        Queue::fake();

        $store = Store::factory()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create();

        $store->delete();

        expect(StorefrontDeployment::query()->whereKey($deployment->getKey())->exists())->toBeTrue();
    });

    it('settles the storefront when a store is deleted outside any panel', function (): void {
        Queue::fake();

        // OffboardResellerAction deletes stores directly, inside a transaction.
        $store = Store::factory()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create([
            'desired_state' => StorefrontDesiredState::Running,
        ]);

        DB::transaction(fn () => $store->delete());

        expect($deployment->fresh()?->desired_state)->toBe(StorefrontDesiredState::Stopped);

        Queue::assertPushed(ReconcileStorefrontJob::class);
    });
});

describe('force delete', function (): void {
    it('queues a destroy carrying the slug, because the row goes with the store', function (): void {
        Queue::fake();

        $store = Store::factory()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create(['slug' => 'closing-shop']);

        $store->forceDelete();

        // The cascade has taken the row, which is exactly why the job cannot be
        // addressed by its id.
        expect(StorefrontDeployment::query()->whereKey($deployment->getKey())->exists())->toBeFalse();

        Queue::assertPushed(
            DestroyStorefrontJob::class,
            fn (DestroyStorefrontJob $job): bool => $job->slug === 'closing-shop',
        );
        Queue::assertNotPushed(ReconcileStorefrontJob::class);
    });

    it('destroys the container by slug with no deployment row left to read', function (): void {
        $destroyed = [];

        $provisioner = Mockery::mock(StorefrontProvisioner::class);
        $provisioner->shouldReceive('destroy')
            ->once()
            ->with(Mockery::on(function (StorefrontReference $reference) use (&$destroyed): bool {
                $destroyed[] = $reference->slug;

                return true;
            }));

        app()->instance(StorefrontProvisioner::class, $provisioner);

        // No store, no deployment — the state a force delete leaves behind.
        app()->call([new DestroyStorefrontJob('closing-shop'), 'handle']);

        expect($destroyed)->toBe(['closing-shop']);
    });
});

it('does nothing for a store that never had a storefront', function (): void {
    Queue::fake();

    Store::factory()->create()->delete();

    Queue::assertNothingPushed();
});
