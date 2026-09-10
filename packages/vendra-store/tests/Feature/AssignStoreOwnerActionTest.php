<?php

declare(strict_types=1);

use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraStore\Actions\AssignStoreOwnerAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

/*
 | The store package names no reseller: the action takes a SubscriptionSubscriber
 | and writes the plain `stores.reseller_id` key. The reseller model is only the
 | subscriber the monorepo has to hand.
 */
function subscriberWithUnits(int $maxUnits, int $existingStores = 0): Reseller
{
    $reseller = Reseller::factory()->create();

    Subscription::factory()
        ->forSubscriber($reseller)
        ->for(Plan::factory()->maxUnits($maxUnits))
        ->create();

    Store::factory()->count($existingStores)->create(['reseller_id' => $reseller->getKey()]);

    return $reseller;
}

it('moves a store to an owner with room in their plan', function (): void {
    $from = subscriberWithUnits(maxUnits: 2, existingStores: 1);
    $to = subscriberWithUnits(maxUnits: 2);
    $store = Store::factory()->create(['reseller_id' => $from->getKey()]);

    app(AssignStoreOwnerAction::class)->execute($store, $to);

    expect($store->fresh()?->reseller_id)->toBe($to->getKey());
});

it('hands a store back to the platform when no owner is given', function (): void {
    $owner = subscriberWithUnits(maxUnits: 2);
    $store = Store::factory()->create(['reseller_id' => $owner->getKey()]);

    app(AssignStoreOwnerAction::class)->execute($store, null);

    expect($store->fresh()?->reseller_id)->toBeNull();
});

it('refuses an owner whose plan is already full', function (): void {
    $to = subscriberWithUnits(maxUnits: 1, existingStores: 1);
    $store = Store::factory()->create(['reseller_id' => null]);

    expect(fn (): Store => app(AssignStoreOwnerAction::class)->execute($store, $to))
        ->toThrow(SubscriptionLimitException::class)
        ->and($store->fresh()?->reseller_id)->toBeNull();
});

/*
 | Re-selecting the owner a store already has is not a gain, so a full plan must
 | not turn a no-op into a failure.
 */
it('leaves a store with the owner it already has even at the plan limit', function (): void {
    $owner = subscriberWithUnits(maxUnits: 1);
    $store = Store::factory()->create(['reseller_id' => $owner->getKey()]);

    app(AssignStoreOwnerAction::class)->execute($store, $owner);

    expect($store->fresh()?->reseller_id)->toBe($owner->getKey());
});

it('refuses an owner whose subscription has lapsed', function (): void {
    $to = Reseller::factory()->create();
    Subscription::factory()->expired()->forSubscriber($to)->for(Plan::factory()->maxUnits(5))->create();
    $store = Store::factory()->create(['reseller_id' => null]);

    expect(fn (): Store => app(AssignStoreOwnerAction::class)->execute($store, $to))
        ->toThrow(SubscriptionLimitException::class);
});
