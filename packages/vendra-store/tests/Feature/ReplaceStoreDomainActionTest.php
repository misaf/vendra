<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Misaf\VendraStore\Actions\ReplaceStoreDomainAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

it('activates a new domain and retains the previous one as trashed history', function (): void {
    $store = Store::factory()->create();
    $original = StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);

    $new = app(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($new->name)->toBe('new.test')
        ->and($new->active)->toBeTrue()
        ->and($new->trashed())->toBeFalse();

    $previous = StoreDomain::withoutGlobalScopes()->withTrashed()->find($original->getKey());

    expect($previous?->trashed())->toBeTrue()
        ->and($previous?->active)->toBeFalse();

    // Only one active, non-trashed domain resolves the property.
    expect($store->execute(fn () => $store->storeDomains()->where('active', true)->count()))->toBe(1);
});

it('replaces the active domain even when another tenant is current', function (): void {
    $current = Store::factory()->create();
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);

    switchToTestTenant($current);

    $new = app(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($new->store_id)->toBe($store->getKey())
        ->and($store->execute(fn () => $store->storeDomains()->where('active', true)->value('name')))->toBe('new.test');
});

it('keeps replaced history when the property is soft-deleted and restored', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);
    app(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    $store->delete();
    $store->restore();

    // The active domain resolves again; the replaced one stays trashed history.
    expect($store->execute(fn () => $store->storeDomains()->where('active', true)->value('name')))->toBe('new.test')
        ->and($store->execute(fn () => $store->storeDomains()->onlyTrashed()->count()))->toBe(1);
});

it('rejects a domain already active on another property', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->create(['name' => 'old.test', 'active' => true]);

    $otherStore = Store::factory()->create();
    StoreDomain::factory()->for($otherStore)->create(['name' => 'taken.test', 'active' => true]);

    app(ReplaceStoreDomainAction::class)->execute($store, 'taken.test');
})->throws(ValidationException::class);
