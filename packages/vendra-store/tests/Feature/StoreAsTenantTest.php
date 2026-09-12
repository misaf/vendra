<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Services\StoreDomainFinder;
use Misaf\VendraStore\Support\NullStoreResellerResolver;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraTenant\Contracts\HostTenantFinder;
use Misaf\VendraTenant\Contracts\TenantContract;

/*
 | Store IS the tenant. Nothing in here should ever need a second row, a
 | `tenants` table, or a `Store -> Tenant` hop.
 */
it('is the application tenant rather than pointing at one', function (): void {
    $store = Store::factory()->active()->create(['name' => 'Acme Flowers', 'slug' => 'acme']);

    expect($store)->toBeInstanceOf(TenantContract::class)
        ->and(Config::get('vendra-tenant.model'))->toBe(Store::class)
        ->and(Config::get('multitenancy.tenant_model'))->toBe(Store::class)
        ->and(resolve(TenantResolver::class)->modelClass())->toBe(Store::class)
        ->and($store->getTenantKey())->toBe($store->id)
        ->and($store->getTenantName())->toBe('Acme Flowers')
        ->and($store->getTenantSlug())->toBe('acme')
        ->and(method_exists($store, 'tenant'))->toBeFalse();
});

it('becomes the current tenant and restores the previous context', function (): void {
    $store = Store::factory()->active()->create();
    $resolver = resolve(TenantResolver::class);

    expect($resolver->current())->toBeNull();

    $seen = $resolver->execute($store->getKey(), fn (): ?int => $resolver->currentId());

    expect($seen)->toBe($store->getKey())
        ->and($resolver->current())->toBeNull();
});

it('answers the tenancy engine host-resolution port', function (): void {
    expect(resolve(HostTenantFinder::class))->toBeInstanceOf(StoreDomainFinder::class)
        ->and(Config::get('multitenancy.tenant_finder'))->toBe(StoreDomainFinder::class);
});

it('resolves itself from one of its own domains', function (): void {
    $store = Store::factory()->active()->create(['slug' => 'acme']);
    StoreDomain::factory()->for($store)->create(['name' => 'acme.example.com', 'active' => true]);

    expect(resolve(HostTenantFinder::class)->findForHost('acme.example.com')?->getKey())->toBe($store->getKey())
        ->and(resolve(HostTenantFinder::class)->findForOrigin('https://acme.example.com')?->getKey())->toBe($store->getKey());
});

it('keeps its domains keyed by store_id and scoped to itself', function (): void {
    $first = Store::factory()->active()->create();
    $second = Store::factory()->active()->create();

    StoreDomain::factory()->for($first)->create(['name' => 'first.example.com', 'active' => true]);
    StoreDomain::factory()->for($second)->create(['name' => 'second.example.com', 'active' => true]);

    expect($first->domains()->pluck('name')->all())->toBe(['first.example.com'])
        ->and($second->domains()->pluck('name')->all())->toBe(['second.example.com']);

    $visible = resolve(TenantResolver::class)->execute(
        $first->getKey(),
        fn (): array => StoreDomain::query()->pluck('name')->all(),
    );

    expect($visible)->toBe(['first.example.com']);
});

it('has no billing reseller until a reseller domain supplies one', function (): void {
    $store = Store::factory()->active()->create();

    expect($store->reseller_id)->toBeNull();

    /*
     | The reseller package binds the real resolver; the store package only ever
     | asks through the port, which is why it stays installable without it.
     */
    app()->bind(StoreResellerResolver::class, NullStoreResellerResolver::class);

    expect(resolve(StoreResellerResolver::class)->find(1))->toBeNull();
});

it('keeps resolving through the generic resolver on id and slug', function (): void {
    /*
     | The resolver no longer hard-codes `id`/`slug`; it asks the model. Store
     | answers with exactly those two, so every lookup below must behave the
     | same as it always has.
     */
    $store = Store::factory()->active()->create(['name' => 'Acme Flowers', 'slug' => 'acme']);

    $resolver = resolve(TenantResolver::class);

    expect($store->getKeyName())->toBe('id')
        ->and($store->getTenantSlugName())->toBe('slug')
        ->and($resolver->findByKeyOrSlug($store->getKey())?->getKey())->toBe($store->getKey())
        ->and($resolver->findByKeyOrSlug('acme')?->getKey())->toBe($store->getKey())
        ->and($resolver->findByKeyOrSlug('no-such-store'))->toBeNull()
        ->and($resolver->searchOptions(''))->toBe([$store->getKey() => 'acme'])
        ->and($resolver->searchOptions('acm'))->toBe([$store->getKey() => 'acme'])
        ->and($resolver->searchOptions('zzz'))->toBeEmpty();
});

/*
 | A store's own locale and timezone reach the tenancy engine through the
 | contract, never by the engine naming a Store. Left unset they mean "keep the
 | platform's", which is what most stores want.
 */
it('presents its own locale and timezone to the tenancy engine', function (): void {
    $store = Store::factory()->active()->create([
        'locale' => 'en',
        'timezone' => 'Europe/Berlin',
    ]);

    expect($store->getTenantLocale())->toBe('en')
        ->and($store->getTenantTimezone())->toBe('Europe/Berlin');
});

it('states no locale or timezone preference when its columns are blank', function (): void {
    $store = Store::factory()->active()->create(['locale' => '', 'timezone' => null]);

    expect($store->getTenantLocale())->toBeNull()
        ->and($store->getTenantTimezone())->toBeNull();
});

it('applies the store locale and timezone while it is the current tenant', function (): void {
    $store = Store::factory()->active()->create([
        'locale' => 'en',
        'timezone' => 'Europe/Berlin',
    ]);

    $applied = resolve(TenantResolver::class)->execute(
        $store->getKey(),
        fn (): array => [Config::string('app.locale'), Config::string('app.timezone')],
    );

    expect($applied)->toBe(['en', 'Europe/Berlin']);
});

it('falls back to the fleet defaults for a store with no preference', function (): void {
    $store = Store::factory()->active()->create(['locale' => null, 'timezone' => null]);

    $applied = resolve(TenantResolver::class)->execute(
        $store->getKey(),
        fn (): array => [Config::string('app.locale'), Config::string('app.timezone')],
    );

    expect($applied)->toBe(['fa', 'Asia/Tehran']);
});

it('falls back to the platform currency and reads platform metadata', function (): void {
    Config::set('money.defaultCurrency', 'USD');

    $store = Store::factory()->active()->create([
        'currency' => null,
        'metadata' => ['onboarded_by' => 'console'],
    ]);
    $priced = Store::factory()->active()->create(['currency' => 'IRR']);

    expect($store->resolvedCurrency())->toBe('USD')
        ->and($priced->resolvedCurrency())->toBe('IRR')
        ->and($store->metadata('onboarded_by'))->toBe('console')
        ->and($store->metadata('missing', 'fallback'))->toBe('fallback');
});
