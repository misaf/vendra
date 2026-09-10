# Vendra Store

The ecommerce **Store** domain for Laravel: the concrete tenant, its domains, and
its storefront lifecycle.

A Store *is* the tenant — it implements `Misaf\VendraTenant\Contracts\TenantContract`
rather than pointing at a separate tenant row — so `stores` is the only table
describing it, and it is the isolation boundary for everything inside it:
products, orders, customers and vendors. A store has a domain, an optional
billing owner, and one storefront; this package decides *when* that storefront
should be deployed, started, stopped, or destroyed and *what* it should contain.

How a runtime is made to agree is somebody else's problem. Everything crossing
that line goes through one typed port, `StorefrontProvisioner`, whose interface
never mentions containers — the shipped implementation happens to use them.

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/laravel-docker-engine` for the shipped provisioner, plus `misaf/vendra-tenant`,
  `misaf/vendra-subscription`, `misaf/vendra-permission`, `misaf/vendra-user`
  and `misaf/vendra-support`

`misaf/vendra-reseller` is **not** a requirement. A store's billing owner is
reached through `Contracts\StoreOwnerResolver`, which the reseller package binds;
without it a store simply has no owner.

## Installation

```bash
composer require misaf/vendra-store
php artisan vendor:publish --tag=vendra-store-config
php artisan migrate
```

The package ships the `stores` migration (`stores`, `store_user`,
`store_domains`) and the `storefront_deployments` migration.

## Store as tenant

Point the tenancy engine at the Store in `config/vendra-tenant.php`:

```php
return [
    'model'       => Misaf\VendraStore\Models\Store::class,
    'foreign_key' => 'tenant_id',
];
```

The foreign key stays the neutral `tenant_id` on purpose: reusable domain
packages (`products`, `blog_posts`, `roles`) are owned through that one column
and keep working under any tenant model. `$product->tenant` resolves to a Store
because a Store is what plays the tenant role. Only records describing the Store
itself — `store_domains`, `storefront_deployments` — name it outright with
`store_id`, and those are owned here.

### Store status

A store's condition is spread over three columns, each written by a different
concern: `provisioning_status` by the provisioner, `active` by an operator, and
`billing_suspended_at` by subscription enforcement. `Store::status()` derives the
one reading an operator wants — `StoreStatus::Pending`, `Provisioning`, `Active`,
`Suspended` or `Failed` — and `Store::query()->withStatus(...)` is that same rule
expressed as SQL. It is derived rather than stored, so no fourth column can drift
out of step with the three that own it.

A store also carries its own `locale`, `currency`, `timezone`, and a free-form
`metadata` bag. Locale and timezone reach the tenancy engine through
`TenantContract`, so a store keeps its own language and clock while it is
current; leaving them null means "follow the platform". Store *settings* are the
tenant-scoped `spatie/laravel-settings` rows, not a column here.

Not every setting belongs to a store. `Settings\StoreCreationSettings` (group
`store_creation`, `global` repository) says whether the platform is creating
stores at all, and `Support\StoreCreationPolicy` is what both store-creating
panels read. It lives here rather than in the console because
`misaf/vendra-reseller` must honour the same rule and sits below the console.

## Configuration

`config/vendra-store.php` describes what a storefront *is*. Runtime drivers,
endpoints, API negotiation, TLS, and timeouts belong to
`misaf/laravel-docker-engine` and its `config/container.php`.
Storefront container images are records in
`storefront_images`, managed by the console. Each deployment references the
selected record, so different stores run different approved builds.

```dotenv
STOREFRONT_NETWORK=traefik-public
STOREFRONT_NAME_PREFIX=vendra-storefront-
STOREFRONT_PORT=3000
STOREFRONT_HEALTH_PATH=/api/health
STOREFRONT_HEALTH_TIMEOUT=120
STOREFRONT_PULL=true
STOREFRONT_CPUS=0.5
STOREFRONT_MEMORY_MB=512
STOREFRONT_MEMORY_RESERVATION_MB=0
STOREFRONT_PIDS_LIMIT=512
STOREFRONT_BASE_DOMAIN
STOREFRONT_API_URL=
STOREFRONT_CERT_RESOLVER=
```

Every storefront in the fleet is capped, because one busy storefront on a shared
host is how the rest of them get slow. `0` or an empty value lifts that cap,
which is what a single-store box wants. The caps are applied through the Engine's
own `HostConfig` keys — `NanoCpus`, `Memory`, `MemoryReservation`, `PidsLimit` —
so Docker and Podman's compatibility socket read them identically, and a redeploy
or a reconciliation pass picks up a changed cap when it recreates the container.

`STOREFRONT_PIDS_LIMIT` bounds processes *and* threads. Memory and CPU caps do
nothing against a runaway that keeps forking, so this is the one that keeps the
host's `pid_max` out of reach; 512 leaves a Next.js server, which holds a few
dozen, plenty of room.

The platform does not create the network. The network, the reverse proxy, and
the TLS material belong to whoever runs the estate; deployment fails with a
pointed error when the network is absent rather than inventing one the proxy is
not attached to.

Every infrastructure setting is read through the injected
`Support\StorefrontSettings` value object. It is bound with
`bind()`, so a configuration change is picked up on the next resolve.

## Usage

### Provisioning a store

```php
use Misaf\VendraStore\Actions\ProvisionStoreAction;

['tenant' => $tenant, 'user' => $user, 'password' => $password] = app(ProvisionStoreAction::class)
    ->execute(
        data: ['domain' => 'flowers-a.com', 'email' => 'owner@flowers-a.com'],
        shouldSeed: true,
        reseller: $reseller,
    );
```

The console and the reseller panel both call this and differ only in which
reseller they resolve, so the flow exists once. It creates the tenant, the owner
user, and the administrator role, then queues the work that finishes
provisioning.

### Reassigning a store's owner

```php
use Misaf\VendraStore\Actions\AssignStoreOwnerAction;

app(AssignStoreOwnerAction::class)->execute($store, $reseller);  // hand it over
app(AssignStoreOwnerAction::class)->execute($store, null);       // take it back
```

The receiving owner gains a store, so this runs the same row lock and
`StoreQuota` check as creating one, and throws `SubscriptionLimitException` when
their plan is full. Re-selecting the owner a store already has is a no-op rather
than a quota failure. The action names no reseller — the owner is a
`SubscriptionSubscriber` — which is what keeps this package installable without
`misaf/vendra-reseller`.

### Operating and offboarding a store

`SuspendStoreAction` and `ReactivateStoreAction` own operator availability;
reactivation is allowed only after tenant provisioning is ready.
`RetryStoreProvisioningAction` resets a failed provisioning attempt and queues
the existing completion job. Storefront operations use the existing
start/stop/restart actions, while `RedeployStoreStorefrontAction` and
`RetryFailedStorefrontDeploymentAction` queue the established deployment job.

`OffboardStoreAction` is the supported removal path. It records a required
reason and prior active state in metadata, inactivates and soft-deletes the
store, and lets `StoreObserver` stop its storefront. Historical domains,
subscriptions, ownership, and tenant data are retained. Restore through
`RestoreOffboardedStoreAction`, which rechecks owner/quota constraints and only
restores active service when provisioning is ready.

### Deploying a storefront

```php
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;

$deployment = app(RequestStorefrontDeploymentAction::class)->execute($tenant, 'flowers-a.com', $form);
```

`RequestStorefrontDeploymentAction` → `ProvisionStorefrontJob` → `StorefrontDeployment`
is the only path. The job runs on its own `storefronts` queue
(`ProvisionStorefrontJob::QUEUE`), served by the single worker that holds a
runtime socket.

The console create wizard may explicitly turn `create_storefront` off. The
shared `CreateStorePage` then creates the store and domain without calling
`RequestStorefrontDeploymentAction`; this is useful when local storefront source
runs outside the managed container runtime.

Status is written only through the model's `markProcessing()`, `markReady()`,
`markRequested()` and `markFailed()`, which enforce the
`Enums\StorefrontDeploymentStatus` transition table and throw
`InvalidStorefrontTransitionException` otherwise. A job attempt that throws with
retries left stays `Processing`; only `ProvisionStorefrontJob::failed()` writes
`Failed`.

### The provisioner port

| Method | Purpose |
| --- | --- |
| `provision(StorefrontProvisionRequest)` | Places the storefront, replacing any predecessor. |
| `start` / `stop` / `restart` | Lifecycle, by `StorefrontReference`. |
| `destroy(StorefrontReference)` | Removes it entirely; absent is success. |
| `observe(StorefrontReference)` | A `StorefrontObservation` of what is actually running. |
| `logs(StorefrontReference, int $lines = 200)` | Recent output, for diagnosing a failure. |

Idempotence is contractual: provisioning twice leaves one storefront, starting a
running one succeeds, stopping a stopped one succeeds. Reconciliation and retry
depend on it. `observe()` must never answer "absent" for a runtime it could not
reach — a converge pass would read that as a missing storefront and rebuild a
healthy one.

`Services\ContainerStorefrontProvisioner` is the one implementation, bound in
`StoreServiceProvider`. Callers type-hint the contract and never learn which
adapter answered.

## Commands

```bash
php artisan storefront:status [--runtime]     # deployments from the database
php artisan storefront:reconcile [--sync]     # converge every runtime with intent
php artisan storefront:redeploy [--sync]      # rebuild everything meant to be up
php artisan storefront:retry-failed [--sync]  # retry deployments marked failed
php artisan storefront:lifecycle {start|stop|restart|status|logs} {slug}
```

`storefront:reconcile` is cheap and safe to repeat: it corrects with the
narrowest verb that works, so a converged estate comes through a pass untouched.
Reach for `storefront:redeploy` only for a change convergence cannot see — an
image republished under the same reference, or an edge label that only a fresh
container will carry. `storefront:lifecycle` records intent, so a storefront
stopped there stays stopped through the next pass.

## Filament

This package ships the shared building blocks — `Filament\Pages\CreateStorePage`,
`Filament\Schemas\StorefrontConfigurationFields`, `Filament\Actions\ReplaceDomainAction`,
`Filament\Concerns\BuildsDailyTrend` — rather than panel resources. The console
and reseller panels own those and differ only in which reseller they resolve as
the store's billing owner.

## Testing

Use the shared `fakeDockerEngine()` transport helper; it registers an SDK-backed
test driver through `Misaf\LaravelDockerEngine\ContainerManager`, so no test here
needs a real daemon.

```bash
php artisan test --compact --testsuite=vendra-store
```

## License

MIT. See [LICENSE](LICENSE).
