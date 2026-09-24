<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StoreStatusCounts;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

/*
 | A store's condition is spread across three columns, each written by a
 | different concern. `status()` is the one reading administrators get, and
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
    'disabled by administrator' => [TenantProvisioningStatus::Ready, false, false, StoreStatus::Suspended],
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

it('filters by any of several statuses', function (): void {
    $pending = Store::factory()->provisioningPending()->active()->create();
    Store::factory()->active()->create();
    $suspended = Store::factory()->active()->suspended()->create();

    expect(Store::query()->withAnyStatus([StoreStatus::Pending, StoreStatus::Suspended])->orderBy('id')->pluck('id')->all())
        ->toBe([$pending->id, $suspended->id]);
});

it('treats an active store as the one that may serve requests', function (): void {
    $active = Store::factory()->active()->create();
    $suspended = Store::factory()->active()->suspended()->create();

    expect(Store::query()->accessible()->pluck('id')->all())->toBe([$active->id])
        ->and(StoreStatus::Provisioning->isSettled())->toBeFalse()
        ->and(StoreStatus::Failed->isSettled())->toBeTrue();
});

it('counts stores per status in one query by the same rule its accessor reads', function (): void {
    Store::factory()->provisioningPending()->active()->create();
    Store::factory()->provisioning()->active()->create();
    Store::factory()->count(2)->provisioningFailed()->active()->create();
    Store::factory()->count(3)->active()->create();
    Store::factory()->active()->suspended()->create();
    Store::factory()->create(['active' => false, 'provisioning_status' => TenantProvisioningStatus::Ready]);
    Store::factory()->active()->create()->delete();

    DB::enableQueryLog();

    $counts = StoreStatusCounts::for();

    expect(DB::getQueryLog())->toHaveCount(1)
        ->and($counts->count(StoreStatus::Pending))->toBe(1)
        ->and($counts->count(StoreStatus::Provisioning))->toBe(1)
        ->and($counts->count(StoreStatus::Failed))->toBe(2)
        ->and($counts->count(StoreStatus::Active))->toBe(3)
        ->and($counts->count(StoreStatus::Suspended))->toBe(2)
        ->and($counts->total())->toBe(9)
        ->and($counts->needingAttention())->toBe(4);
});

it('counts only the stores the given query selects', function (): void {
    Store::factory()->count(2)->active()->create(['reseller_id' => 7]);
    Store::factory()->provisioningFailed()->active()->create(['reseller_id' => 7]);
    Store::factory()->count(4)->active()->create(['reseller_id' => 8]);

    $counts = StoreStatusCounts::for(Store::query()->where('reseller_id', 7));

    expect($counts->count(StoreStatus::Active))->toBe(2)
        ->and($counts->needingAttention())->toBe(1)
        ->and($counts->total())->toBe(3);
});

it('filters by the status of the store\'s storefront deployment', function (): void {
    $ready = StorefrontDeployment::factory()->create(['status' => StorefrontDeploymentStatus::Ready])->store;
    StorefrontDeployment::factory()->create(['status' => StorefrontDeploymentStatus::Failed]);
    Store::factory()->active()->create();

    expect(Store::query()->withDeploymentStatus(StorefrontDeploymentStatus::Ready)->pluck('id')->all())->toBe([$ready->id]);
});

it('needs attention while provisioning is unsettled or failed, or when the storefront deployment failed', function (): void {
    $pending = Store::factory()->provisioningPending()->active()->create();
    $failed = Store::factory()->provisioningFailed()->active()->create();
    $deploymentFailed = Store::factory()->active()->create();
    StorefrontDeployment::factory()->for($deploymentFailed)->create(['status' => StorefrontDeploymentStatus::Failed]);
    StorefrontDeployment::factory()->for(Store::factory()->active())->create(['status' => StorefrontDeploymentStatus::Ready]);
    Store::factory()->active()->suspended()->create();

    expect(Store::query()->needingAttention()->orderBy('id')->pluck('id')->all())
        ->toBe([$pending->id, $failed->id, $deploymentFailed->id]);
});
