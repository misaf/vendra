<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\AssignStoreResellerAction;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Tests\Fixtures\BillingSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

beforeEach(function (): void {
    BillingSubscriber::createTable();
});

it('moves a store to a reseller with room in their plan', function (): void {
    $from = BillingSubscriber::withPlan(maxUnits: 2, existingStores: 1);
    $to = BillingSubscriber::withPlan(maxUnits: 2);
    $store = Store::factory()->create(['reseller_id' => $from->getKey()]);

    resolve(AssignStoreResellerAction::class)->execute($store, $to);

    expect($store->fresh()?->reseller_id)->toBe($to->getKey());
});

it('hands a store back to the platform when no reseller is given', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 2);
    $store = Store::factory()->create(['reseller_id' => $reseller->getKey()]);

    resolve(AssignStoreResellerAction::class)->execute($store, null);

    expect($store->fresh()?->reseller_id)->toBeNull();
});

it('refuses a reseller whose plan is already full', function (): void {
    $to = BillingSubscriber::withPlan(maxUnits: 1, existingStores: 1);
    $store = Store::factory()->create(['reseller_id' => null]);

    expect(fn (): Store => resolve(AssignStoreResellerAction::class)->execute($store, $to))
        ->toThrow(SubscriptionLimitException::class)
        ->and($store->fresh()?->reseller_id)->toBeNull();
});

/*
 | Re-selecting the reseller a store already has is not a gain, so a full plan must
 | not turn a no-op into a failure.
 */
it('leaves a store with the reseller it already has even at the plan limit', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 1);
    $store = Store::factory()->create(['reseller_id' => $reseller->getKey()]);

    resolve(AssignStoreResellerAction::class)->execute($store, $reseller);

    expect($store->fresh()?->reseller_id)->toBe($reseller->getKey());
});

it('refuses a reseller whose subscription has lapsed', function (): void {
    $to = BillingSubscriber::query()->create();
    Subscription::factory()->expired()->forSubscriber($to)->for(Plan::factory()->active()->maxUnits(5))->create();
    $store = Store::factory()->create(['reseller_id' => null]);

    expect(fn (): Store => resolve(AssignStoreResellerAction::class)->execute($store, $to))
        ->toThrow(SubscriptionLimitException::class);
});

it('refuses to reassign an offboarded store', function (): void {
    $to = BillingSubscriber::withPlan(maxUnits: 2);
    $store = Store::factory()->create(['reseller_id' => null]);
    $store->delete();

    expect(fn (): Store => resolve(AssignStoreResellerAction::class)->execute($store, $to))
        ->toThrow(ModelNotFoundException::class)
        ->and($store->fresh()?->reseller_id)->toBeNull();
});

it("lifts the previous reseller's billing suspension and restarts the storefront", function (): void {
    Queue::fake();
    $to = BillingSubscriber::withPlan(maxUnits: 2);
    $store = Store::factory()->active()->create(['billing_suspended_at' => now()]);
    $deployment = StorefrontDeployment::factory()->for($store)->create(['desired_state' => StorefrontDesiredState::Stopped]);

    resolve(AssignStoreResellerAction::class)->execute($store, $to);

    expect($store->fresh()?->billing_suspended_at)->toBeNull()
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);
    Queue::assertPushed(ReconcileStorefrontJob::class, fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id);
});
