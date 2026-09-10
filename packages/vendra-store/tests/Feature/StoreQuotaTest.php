<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraReseller\Actions\CreateResellerAction;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraReseller\Models\ResellerUser;
use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

function resellerWithPlan(int $maxUnits, int $existingProperties = 0): Reseller
{
    $reseller = Reseller::factory()->create();

    Subscription::factory()
        ->forSubscriber($reseller)
        ->for(Plan::factory()->maxUnits($maxUnits))
        ->create();

    for ($index = 0; $index < $existingProperties; $index++) {
        createTestTenant(['reseller_id' => $reseller->getKey()]);
    }

    return $reseller;
}

it('allows creating a property below the plan limit', function (): void {
    $reseller = resellerWithPlan(maxUnits: 2, existingProperties: 1);
    $quota = resolve(StoreQuota::class);

    expect($quota->canCreateStore($reseller))->toBeTrue()
        ->and($quota->remainingStores($reseller))->toBe(1);

    $quota->assertCanCreateStore($reseller);
});

it('blocks creating a property at the plan limit', function (): void {
    $reseller = resellerWithPlan(maxUnits: 1, existingProperties: 1);
    $quota = resolve(StoreQuota::class);

    expect($quota->canCreateStore($reseller))->toBeFalse()
        ->and($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class);

it('blocks property creation when no subscription is active', function (): void {
    $reseller = Reseller::factory()->create();
    Subscription::factory()->expired()->forSubscriber($reseller)->for(Plan::factory()->maxUnits(5))->create();

    $quota = resolve(StoreQuota::class);

    expect($quota->canCreateStore($reseller))->toBeFalse()
        ->and($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class);

it('blocks property creation for an inactive reseller with an active subscription', function (): void {
    $reseller = resellerWithPlan(maxUnits: 2);
    $reseller->update(['active' => false]);

    $quota = resolve(StoreQuota::class);

    expect($quota->canCreateStore($reseller))->toBeFalse()
        ->and($quota->remainingStores($reseller))->toBe(0);

    $quota->assertCanCreateStore($reseller);
})->throws(SubscriptionLimitException::class, 'is inactive');

it('creates a reseller subscribed to a plan for its period', function (): void {
    $plan = Plan::factory()->period(PeriodUnit::Month, 1)->create();

    $result = resolve(CreateResellerAction::class)->execute(
        plan: $plan,
        username: 'acme_owner',
        email: 'owner@acme.test',
        password: 'Secure123',
    );

    expect(Arr::get($result, 'reseller'))->toBeInstanceOf(Reseller::class)
        ->and(Arr::get($result, 'reseller')->exists)->toBeTrue()
        ->and(Arr::get($result, 'owner'))->toBeInstanceOf(ResellerUser::class)
        ->and(Arr::get($result, 'owner')->reseller_id)->toBe(Arr::get($result, 'reseller')->getKey())
        ->and(Arr::get($result, 'subscription')->status)->toBe(SubscriptionStatus::Active)
        ->and(Arr::get($result, 'subscription')->plan_id)->toBe($plan->getKey())
        ->and(Arr::get($result, 'subscription')->ends_at->toDateString())
        ->toBe(Arr::get($result, 'subscription')->starts_at->copy()->addMonth()->toDateString())
        ->and(Arr::get($result, 'reseller')->activeSubscription()?->getKey())->toBe(Arr::get($result, 'subscription')->getKey());
});

it('creates a reseller with the requested active state', function (): void {
    $result = resolve(CreateResellerAction::class)->execute(
        plan: Plan::factory()->create(),
        username: 'paused_owner',
        email: 'owner@paused.test',
        password: 'Secure123',
        active: false,
    );

    expect(Arr::get($result, 'reseller')->active)->toBeFalse();
});
