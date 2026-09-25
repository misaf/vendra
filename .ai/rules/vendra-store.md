---
paths:
  - 'packages/vendra-store/**'
---

# Vendra Store

## Store IS the tenant — there is no Store → Tenant hop
`Models\Store` extends Spatie's tenant and implements `Misaf\VendraTenant\Contracts\TenantContract`. `stores` is the only table describing it; there is no `tenants` table and no 1:1 pair. It is wired to the engine in `config/vendra-tenant.php` (`model`, `relation`) and `config/multitenancy.php` (`tenant_model`, `tenant_finder`). `Store::accessible()` — active, provisioning-ready, not billing-suspended — is the request-serving boundary for both `StoreDomainFinder` and the resolver's search options.

## Two row-ownership columns, two mechanisms
Reusable domain packages own their rows through the **neutral `tenant_id`** and `Misaf\VendraSupport\Tenancy\BelongsToTenant`; that column resolves to a Store only because a Store is what plays the tenant role. Records that describe the Store itself — `store_domains`, `storefront_deployments` — carry **`store_id`** and use this package's `Concerns\BelongsToStore` / `Scopes\StoreScope`. Never put `store_id` in a reusable package's table, and never register a `store_id` table in the support `TenantTableRegistry` (it drives the `tenant_id` retrofit).

## The store domain must not depend on the reseller domain
`misaf/vendra-reseller` is not a dependency of the store package, not even a dev one, and the store test suite does not import it: tests stand in for the reseller with `Tests\Fixtures\BillingSubscriber` (a `SubscriptionSubscriber` with its own table and `withPlan()`) and bind `Tests\Fixtures\BillingSubscriberResolver` when an action resolves the reseller by key. Reseller-specific behavior is tested in `vendra-reseller`. A store's billing reseller arrives through `Contracts\StoreResellerResolver` (default `Support\NullStoreResellerResolver`; the reseller package binds the real one), and creation takes `(Model&SubscriptionSubscriber)|null $reseller` — `ProvisionStoreAction::execute(..., ?SubscriptionSubscriber $reseller = null)`, `CreateStorePage::resolveReseller()`. `stores.reseller_id` is a plain nullable indexed column with no cross-package foreign key; `$store->reseller()` and `$reseller->stores()` are both registered by `ResellerServiceProvider`. A null reseller means a store the console owns directly, not an error. The host's `tests/Feature/ArchTest.php` and `tests/Feature/PackageDependencyGraphTest.php` enforce the direction, including in the store test suite.

## Use Laravel Docker Engine for storefront runtime
Resolve storefront Engine access through Misaf\LaravelDockerEngine\ContainerManager via StorefrontContainerRuntime. Keep Docker and Podman access API-only, select the driver with container.default, and use the shared SDK transport fake in tests. Do not reintroduce vendra-container.
