<?php

declare(strict_types=1);

use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraStore\Tests\Fixtures\BillingSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

beforeEach(function (): void {
    BillingSubscriber::createTable();
});

it('allows creating a property below the plan limit', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 2, existingStores: 1);
    $quota = resolve(StoreQuota::class);

    expect($quota->remainingStores($reseller))->toBe(1);

    $quota->assertCanCreateStore($reseller);
});

it('blocks creating a property at the plan limit', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 1, existingStores: 1);
    $quota = resolve(StoreQuota::class);

    expect($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class);

it('blocks property creation when no subscription is active', function (): void {
    $reseller = BillingSubscriber::query()->create();
    Subscription::factory()->expired()->forSubscriber($reseller)->for(Plan::factory()->active()->maxUnits(5))->create();

    $quota = resolve(StoreQuota::class);

    expect($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class);

it('blocks property creation for an inactive reseller with an active subscription', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 2);
    $reseller->update(['active' => false]);

    $quota = resolve(StoreQuota::class);

    expect($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class, 'is inactive');
