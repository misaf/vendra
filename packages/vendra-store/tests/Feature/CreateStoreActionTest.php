<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Misaf\VendraReseller\Actions\OffboardResellerAction;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraStore\Actions\CreateStoreAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraUser\Models\User;

function subscribedReseller(int $maxUnits): Reseller
{
    $reseller = Reseller::factory()->create();

    Subscription::factory()
        ->forSubscriber($reseller)
        ->for(Plan::factory()->maxUnits($maxUnits))
        ->create();

    return $reseller;
}

it('stamps the owning reseller on a store created under it', function (): void {
    $reseller = subscribedReseller(maxUnits: 2);

    $result = resolve(CreateStoreAction::class)->execute(
        name: 'Acme Store',
        domain: 'acme.test',
        username: 'admin_acme',
        email: 'admin@acme.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    expect(Arr::get($result, 'store')->reseller_id)->toBe($reseller->getKey())
        ->and($reseller->stores()->count())->toBe(1);
});

it('rejects creating a store once the reseller reaches its plan limit', function (): void {
    $reseller = subscribedReseller(maxUnits: 1);

    resolve(CreateStoreAction::class)->execute(
        name: 'First Store',
        domain: 'first.test',
        username: 'admin_first',
        email: 'admin@first.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    resolve(CreateStoreAction::class)->execute(
        name: 'Second Store',
        domain: 'second.test',
        username: 'admin_second',
        email: 'admin@second.test',
        password: 'secret-password',
        reseller: $reseller,
    );
})->throws(SubscriptionLimitException::class);

it('rejects creating a store for an offboarded reseller', function (): void {
    $reseller = subscribedReseller(maxUnits: 2);
    resolve(OffboardResellerAction::class)->execute($reseller, 'Contract ended');

    expect(fn (): array => resolve(CreateStoreAction::class)->execute(
        name: 'Orphan Store',
        domain: 'orphan.test',
        username: 'admin_orphan',
        email: 'admin@orphan.test',
        password: 'secret-password',
        reseller: $reseller,
    ))->toThrow(ModelNotFoundException::class)
        ->and(Store::query()->where('name', 'Orphan Store')->exists())->toBeFalse();
});

it('keeps store administrators separate from the reseller user account', function (): void {
    $reseller = subscribedReseller(maxUnits: 3);
    $user = User::factory()->create(['tenant_id' => null]);
    $reseller->user()->associate($user)->save();

    $first = resolve(CreateStoreAction::class)->execute(
        name: 'First Store',
        domain: 'first.test',
        username: 'admin_first',
        email: 'admin@first.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    $second = resolve(CreateStoreAction::class)->execute(
        name: 'Second Store',
        domain: 'second.test',
        username: 'admin_second',
        email: 'admin@second.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    expect(Reseller::forUser($user)?->is($reseller))->toBeTrue()
        ->and(Arr::get($first, 'user'))->toBeInstanceOf(User::class)
        ->and(Arr::get($second, 'user'))->toBeInstanceOf(User::class);
});

it('rejects making one user the main account of two resellers', function (): void {
    $reseller = subscribedReseller(maxUnits: 2);

    expect(fn (): Reseller => Reseller::factory()->create(['user_id' => $reseller->user_id]))
        ->toThrow(QueryException::class);
});

it('still creates a store with no reseller for the legacy path', function (): void {
    $result = resolve(CreateStoreAction::class)->execute(
        name: 'Legacy Store',
        domain: 'legacy.test',
        username: 'admin_legacy',
        email: 'admin@legacy.test',
        password: 'secret-password',
    );

    expect(Arr::get($result, 'store'))->toBeInstanceOf(Store::class)
        ->and(Arr::get($result, 'store')->reseller_id)->toBeNull();
});

it('slugifies the store name so its admin host resolves', function (): void {
    $reseller = subscribedReseller(maxUnits: 2);

    $result = resolve(CreateStoreAction::class)->execute(
        name: 'Houshang Flowers',
        domain: 'houshang.test',
        username: 'admin_houshang',
        email: 'admin@houshang.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    expect(Arr::get($result, 'store')->slug)->toBe('houshang-flowers')
        ->and(StoreDomain::query()->where('name', 'houshang.test')->value('slug'))->toBe('houshangtest');
});
