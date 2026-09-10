<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

it('brings the storefront back up when a soft-deleted store is restored', function (): void {
    Queue::fake();

    $store = Store::factory()->active()->create();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    $store->delete();

    expect($deployment->fresh()?->desired_state)->toBe(StorefrontDesiredState::Stopped);

    $store->restore();

    /*
     | Without this the store is live again — resolving, serving its panel — with
     | a storefront convergence keeps deliberately stopped, because a stopped
     | intent is exactly what reconciliation is built to respect.
     */
    expect($deployment->fresh()?->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(
        ReconcileStorefrontJob::class,
        fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id,
    );
});
