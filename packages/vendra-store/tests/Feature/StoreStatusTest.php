<?php

declare(strict_types=1);

use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

/*
 | A store's condition is spread across three columns, each written by a
 | different concern. `status()` is the one reading operators get, and
 | `withStatus()` is that same reading as SQL — the pair must never disagree.
 */
it('derives each status from the columns that own it', function (TenantProvisioningStatus $provisioning, bool $active, bool $billingSuspended, StoreStatus $expected): void {
    $store = Store::factory()->create([
        'provisioning_status' => $provisioning,
        'active' => $active,
        'billing_suspended_at' => $billingSuspended ? now() : null,
    ]);

    expect($store->status())->toBe($expected);
})->with([
    'pending' => [TenantProvisioningStatus::Pending, true, false, StoreStatus::Pending],
    'provisioning' => [TenantProvisioningStatus::Processing, true, false, StoreStatus::Provisioning],
    'failed' => [TenantProvisioningStatus::Failed, true, false, StoreStatus::Failed],
    'active' => [TenantProvisioningStatus::Ready, true, false, StoreStatus::Active],
    'disabled by operator' => [TenantProvisioningStatus::Ready, false, false, StoreStatus::Suspended],
    'suspended by billing' => [TenantProvisioningStatus::Ready, true, true, StoreStatus::Suspended],
    'provisioning outranks' => [TenantProvisioningStatus::Failed, false, true, StoreStatus::Failed],
]);

it('filters by the same rule its accessor reads', function (): void {
    $stores = [
        [StoreStatus::Pending, Store::factory()->provisioningPending()->active()->create()],
        [StoreStatus::Provisioning, Store::factory()->provisioning()->active()->create()],
        [StoreStatus::Failed, Store::factory()->provisioningFailed()->active()->create()],
        [StoreStatus::Active, Store::factory()->active()->create()],
        [StoreStatus::Suspended, Store::factory()->active()->suspended()->create()],
    ];

    foreach ($stores as [$status, $store]) {
        expect(Store::query()->withStatus($status)->pluck('id')->all())
            ->toBe([$store->id])
            ->and($store->status())->toBe($status);
    }
});

it('treats an active store as the one that may serve requests', function (): void {
    $active = Store::factory()->active()->create();
    $suspended = Store::factory()->active()->suspended()->create();

    expect($active->status()->isServing())->toBeTrue()
        ->and($suspended->status()->isServing())->toBeFalse()
        ->and(Store::query()->accessible()->pluck('id')->all())->toBe([$active->id])
        ->and(StoreStatus::Provisioning->isSettled())->toBeFalse()
        ->and(StoreStatus::Failed->isSettled())->toBeTrue();
});
