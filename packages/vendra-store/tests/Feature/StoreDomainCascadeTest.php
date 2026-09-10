<?php

declare(strict_types=1);

use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

it('soft-deletes a property domains even when another tenant is current', function (): void {
    $current = Store::factory()->create();
    $store = Store::factory()->create();
    $domain = StoreDomain::factory()->for($store)->create(['active' => true]);

    switchToTestTenant($current);

    $store->delete();

    $persisted = StoreDomain::withoutGlobalScopes()->withTrashed()->find($domain->getKey());

    expect($store->fresh()?->trashed())->toBeTrue()
        ->and($persisted?->trashed())->toBeTrue();
});

it('restores trashed domains when a property is restored', function (): void {
    $store = Store::factory()->create();
    $domain = StoreDomain::factory()->for($store)->create(['active' => true]);

    $store->delete();
    $store->restore();

    expect($store->fresh()?->trashed())->toBeFalse()
        ->and($store->execute(fn () => $store->storeDomains()->whereKey($domain->getKey())->exists()))->toBeTrue();
});

it('permanently removes domains when a property is force-deleted', function (): void {
    $store = Store::factory()->create();
    $domain = StoreDomain::factory()->for($store)->create();

    $store->forceDelete();

    expect(Store::withTrashed()->whereKey($store->getKey())->exists())->toBeFalse()
        ->and(StoreDomain::withTrashed()->whereKey($domain->getKey())->exists())->toBeFalse();
});
