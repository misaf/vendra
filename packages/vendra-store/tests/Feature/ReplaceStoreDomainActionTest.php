<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraStore\Actions\ReplaceStoreDomainAction;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;

it('activates a new domain and retains the previous one as trashed history', function (): void {
    $store = Store::factory()->create();
    $original = StoreDomain::factory()->for($store)->primary()->create(['name' => 'old.test']);

    $new = resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($new->name)->toBe('new.test')
        ->and($new->active)->toBeTrue()
        ->and($new->is_primary)->toBeTrue()
        ->and($new->trashed())->toBeFalse();

    $previous = StoreDomain::query()->withoutGlobalScopes()->withTrashed()->find($original->getKey());

    expect($previous?->trashed())->toBeTrue()
        ->and($previous?->active)->toBeFalse()
        ->and($previous?->is_primary)->toBeFalse();

    // Only one active, non-trashed domain resolves the property.
    expect($store->execute(fn () => $store->storeDomains()->where('active', true)->count()))->toBe(1);
});

it('keeps alias domains when replacing the primary domain', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'old.test']);
    $alias = StoreDomain::factory()->for($store)->active()->create(['name' => 'alias.test']);

    resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($alias->fresh()?->trashed())->toBeFalse()
        ->and($alias->fresh()?->active)->toBeTrue()
        ->and($store->primaryDomain()->value('name'))->toBe('new.test');
});

it('allows only one live primary domain per store', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'first.test']);

    expect(fn () => StoreDomain::factory()->for($store)->primary()->create(['name' => 'second.test']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('replaces the active domain even when another tenant is current', function (): void {
    $current = Store::factory()->create();
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'old.test']);

    switchToTestTenant($current);

    $new = resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    expect($new->store_id)->toBe($store->getKey())
        ->and($store->execute(fn () => $store->storeDomains()->where('active', true)->value('name')))->toBe('new.test');
});

it('keeps replaced history when the property is soft-deleted and restored', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'old.test']);
    resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test');

    $store->delete();
    $store->restore();

    // The active domain resolves again; the replaced one stays trashed history.
    expect($store->execute(fn () => $store->storeDomains()->where('active', true)->value('name')))->toBe('new.test')
        ->and($store->execute(fn () => $store->storeDomains()->onlyTrashed()->count()))->toBe(1);
});

it('refuses to replace the domain of an offboarded store', function (): void {
    $store = Store::factory()->create();
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'old.test']);
    $store->delete();

    expect(fn () => resolve(ReplaceStoreDomainAction::class)->execute($store, 'new.test'))
        ->toThrow(ModelNotFoundException::class);
});

it('rejects domains that collide with an administration host or a retained deployment', function (string $domain): void {
    StorefrontDeployment::factory()->create(['domain' => 'held.test']);

    expect(Validator::make(['domain' => $domain], ['domain' => StoreDomain::activeDomainRules()])->fails())->toBeTrue();
})->with(['admin.shop.test', 'held.test']);
