<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\ReactivateStoreForBillingAction;
use Misaf\VendraStore\Actions\SuspendStoreForBillingAction;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

beforeEach(function (): void {
    Queue::fake();
});

it('stops the storefront of a store suspended for billing and starts it on reactivation', function (): void {
    $store = Store::factory()->active()->create();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    resolve(SuspendStoreForBillingAction::class)->execute($store);

    expect($store->refresh()->billing_suspended_at)->not->toBeNull()
        ->and($store->status())->toBe(StoreStatus::Suspended)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    resolve(ReactivateStoreForBillingAction::class)->execute($store);

    expect($store->refresh()->billing_suspended_at)->toBeNull()
        ->and($store->status())->toBe(StoreStatus::Active)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(ReconcileStorefrontJob::class, fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id);
});

it('keeps an administratively suspended store down when its billing is reactivated', function (): void {
    $store = Store::factory()->active()->create(['active' => false, 'billing_suspended_at' => now()]);
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);

    resolve(ReactivateStoreForBillingAction::class)->execute($store);

    expect($store->refresh()->billing_suspended_at)->toBeNull()
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    Queue::assertNotPushed(ReconcileStorefrontJob::class);
});
