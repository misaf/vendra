<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Misaf\VendraStore\Actions\CreateStoreAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Tests\Fixtures\BillingSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;

beforeEach(function (): void {
    BillingSubscriber::createTable();
});

it('stamps the owning reseller on a store created under it', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 2);

    $result = resolve(CreateStoreAction::class)->execute(
        name: 'Acme Store',
        domain: 'acme.test',
        username: 'admin_acme',
        email: 'admin@acme.test',
        password: 'secret-password',
        reseller: $reseller,
    );

    expect(Arr::get($result, 'store')->reseller_id)->toBe($reseller->getKey())
        ->and(Store::query()->where('reseller_id', $reseller->getKey())->count())->toBe(1);
});

it("makes the given domain the store's primary domain", function (): void {
    $result = resolve(CreateStoreAction::class)->execute(
        name: 'Acme Store',
        domain: 'acme.test',
        username: 'admin_acme',
        email: 'admin@acme.test',
        password: 'secret-password',
    );

    expect(Arr::get($result, 'store')->primaryDomain?->name)->toBe('acme.test');
});

it('rejects creating a store once the reseller reaches its plan limit', function (): void {
    $reseller = BillingSubscriber::withPlan(maxUnits: 1);

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
    $reseller = BillingSubscriber::withPlan(maxUnits: 2);
    $reseller->delete();

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
    $reseller = BillingSubscriber::withPlan(maxUnits: 2);

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
