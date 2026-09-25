<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\AddStoreDomainAliasAction;
use Misaf\VendraStore\Actions\CreateStoreAction;
use Misaf\VendraStore\Actions\ReplaceStoreDomainAction;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Tests\Fixtures\BillingSubscriber;
use Misaf\VendraStore\Tests\Fixtures\BillingSubscriberResolver;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use Misaf\VendraSupport\Filament\Widgets\PlanUsageWidget;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Queue::fake();
    BillingSubscriber::createTable();
    app()->bind(StoreResellerResolver::class, BillingSubscriberResolver::class);
    Config::set('vendra-store.storefront.base_domain', 'vendra.test');
});

/**
 * @param  list<string>  $features
 * @param  array<string, int>|null  $limits
 */
function entitledStore(array $features = [], ?array $limits = null): Store
{
    $reseller = BillingSubscriber::withPlan(maxUnits: 5, features: $features, limits: $limits);
    $store = Store::factory()->create(['reseller_id' => $reseller->getKey()]);
    StoreDomain::factory()->for($store)->primary()->create(['name' => 'shop.vendra.test']);

    return $store;
}

function createEntitledStore(BillingSubscriber $reseller, string $domain): Store
{
    return Arr::get(resolve(CreateStoreAction::class)->execute(
        name: 'Acme Store',
        domain: $domain,
        username: 'admin_acme',
        email: 'admin@acme.test',
        password: 'secret-password',
        reseller: $reseller,
    ), 'store');
}

it('tells custom domains from platform subdomains', function (): void {
    expect(StoreDomain::isCustom('shop.vendra.test'))->toBeFalse()
        ->and(StoreDomain::isCustom('SHOP.Vendra.Test'))->toBeFalse()
        ->and(StoreDomain::isCustom('shop.example.com'))->toBeTrue()
        ->and(StoreDomain::isCustom('evilvendra.test'))->toBeTrue();

    Config::set('vendra-store.storefront.base_domain', '');

    expect(StoreDomain::isCustom('shop.example.com'))->toBeFalse();
});

describe('custom domains', function (): void {
    it('refuses a custom domain for a new store without the feature', function (): void {
        createEntitledStore(BillingSubscriber::withPlan(maxUnits: 1), 'shop.example.com');
    })->throws(EntitlementExceededException::class);

    it('creates stores on a platform subdomain or, with the feature, a custom domain', function (): void {
        expect(createEntitledStore(BillingSubscriber::withPlan(maxUnits: 1), 'shop.vendra.test')->exists)->toBeTrue()
            ->and(createEntitledStore(BillingSubscriber::withPlan(maxUnits: 1, features: ['custom_domain']), 'shop.example.com')->exists)->toBeTrue();
    });

    it('lets the console own a store on a custom domain', function (): void {
        $result = resolve(CreateStoreAction::class)->execute(
            name: 'Acme Store',
            domain: 'shop.example.com',
            username: 'admin_acme',
            email: 'admin@acme.test',
            password: 'secret-password',
        );

        expect(Arr::get($result, 'store')->primaryDomain?->name)->toBe('shop.example.com');
    });

    it('refuses a custom alias or replacement without the feature', function (string $action): void {
        $store = entitledStore();

        expect(fn () => resolve($action)->execute($store, 'shop.example.com'))
            ->toThrow(EntitlementExceededException::class)
            ->and($store->domains()->pluck('name')->all())->toBe(['shop.vendra.test']);
    })->with([AddStoreDomainAliasAction::class, ReplaceStoreDomainAction::class]);

    it('adds a custom alias and replaces with a custom domain with the feature', function (): void {
        $store = entitledStore(features: ['custom_domain']);

        resolve(AddStoreDomainAliasAction::class)->execute($store, 'alias.example.com');
        resolve(ReplaceStoreDomainAction::class)->execute($store, 'shop.example.com');

        expect($store->domains()->pluck('name')->sort()->values()->all())->toBe(['alias.example.com', 'shop.example.com']);
    });
});

it('stops adding aliases at the plan domain limit', function (): void {
    $store = entitledStore(limits: [PlanLimit::DomainsPerStore->value => 2]);

    resolve(AddStoreDomainAliasAction::class)->execute($store, 'second.vendra.test');

    expect(fn () => resolve(AddStoreDomainAliasAction::class)->execute($store, 'third.vendra.test'))
        ->toThrow(EntitlementExceededException::class, 'Domains per store (2)')
        ->and($store->domains()->count())->toBe(2);
});

describe('store entitlements', function (): void {
    it('leaves a console-owned store unrestricted', function (): void {
        $store = Store::factory()->create();
        $entitlements = resolve(TenantEntitlements::class);

        expect($entitlements->allows(PlanFeature::CustomDomain, $store))->toBeTrue()
            ->and($entitlements->limit(PlanLimit::ProductsPerStore, $store))->toBeNull();
    });

    it('reads the plan of the store reseller and treats a missing limit as unlimited', function (): void {
        $store = entitledStore(features: ['custom_domain'], limits: [PlanLimit::ProductsPerStore->value => 10]);
        $entitlements = resolve(TenantEntitlements::class);

        expect($entitlements->allows(PlanFeature::CustomDomain, $store))->toBeTrue()
            ->and($entitlements->allows(PlanFeature::PrioritySupport, $store))->toBeFalse()
            ->and($entitlements->limit(PlanLimit::ProductsPerStore, $store))->toBe(10)
            ->and($entitlements->limit(PlanLimit::DomainsPerStore, $store))->toBeNull();
    });

    it('keeps the lapsed plan limits and allows nothing without any subscription', function (): void {
        $lapsed = BillingSubscriber::query()->create();
        Subscription::factory()->expired()->forSubscriber($lapsed)
            ->for(Plan::factory()->active()->withLimits([PlanLimit::ProductsPerStore->value => 7]))
            ->create();
        $unsubscribed = BillingSubscriber::query()->create();
        $entitlements = resolve(TenantEntitlements::class);

        $lapsedStore = Store::factory()->create(['reseller_id' => $lapsed->getKey()]);
        $unsubscribedStore = Store::factory()->create(['reseller_id' => $unsubscribed->getKey()]);

        expect($entitlements->limit(PlanLimit::ProductsPerStore, $lapsedStore))->toBe(7)
            ->and($entitlements->limit(PlanLimit::ProductsPerStore, $unsubscribedStore))->toBe(0)
            ->and($entitlements->allows(PlanFeature::CustomDomain, $unsubscribedStore))->toBeFalse();
    });
});

describe('plan usage widget', function (): void {
    it('shows the current store its usage of each limited plan limit', function (): void {
        $store = entitledStore(limits: [PlanLimit::DomainsPerStore->value => 2]);
        $store->makeCurrent();

        expect(PlanUsageWidget::canView())->toBeTrue();

        livewire(PlanUsageWidget::class)
            ->assertSee(PlanLimit::DomainsPerStore->getLabel())
            ->assertSee('1 / 2')
            ->assertDontSee(PlanLimit::ProductsPerStore->getLabel());
    });

    it('stays hidden for a store without limits', function (): void {
        entitledStore()->makeCurrent();

        expect(PlanUsageWidget::canView())->toBeFalse();
    });
});
