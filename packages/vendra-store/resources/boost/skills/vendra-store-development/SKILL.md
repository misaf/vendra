---
name: vendra-store-development
description: "Create, modify, review, or test the Vendra Store module in packages/vendra-store, changing the store domain and the storefront deployment lifecycle. Use for StorefrontProvisioner, ContainerStorefrontProvisioner, StorefrontDeployment, StorefrontDeploymentStatus, StorefrontDesiredState, StorefrontRuntimeState, StorefrontReconciliationOutcome, CreateStoreAction, ProvisionStoreAction, DeleteStoreAction, DeployStoreStorefrontAction, DestroyStoreStorefrontAction, StartStoreStorefrontAction, StopStoreStorefrontAction, RestartStoreStorefrontAction, ReconcileStoreStorefrontAction, RequestStorefrontDeploymentAction, ProvisionStorefrontJob, DestroyStorefrontJob, ReconcileStorefrontJob, CompleteStoreProvisioningJob, StorefrontSettings, StorefrontConfigurationMap, StorefrontConfigurationValidator, StorefrontContainerDefinitionFactory, StorefrontOrigins, StoreQuota, CreateStorePage, and the storefront console commands."
---

# Vendra Store

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-store` from the project root.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

## Module Boundary

- This package owns the **ecommerce Store**: the concrete tenant model, its domains, and its storefront lifecycle. It owns *what* a storefront should be and *when* it should change; it never owns *how* a runtime is driven — that lives behind `Contracts\StorefrontProvisioner`.

## Store As Tenant

- `Models\Store` implements `Misaf\VendraTenant\Contracts\TenantContract` and extends Spatie's tenant, so **Store IS the tenant**: there is no `tenants` table, no `Store -> Tenant` relation, and no 1:1 pair. It is wired in `config/vendra-tenant.php` (`model`, `relation`) and `config/multitenancy.php` (`tenant_model`, `tenant_finder`).
- `Store::accessible()` — active, provisioning-ready, not billing-suspended — is the request-serving boundary. Both `Services\StoreDomainFinder` and the engine's resolver search options go through it; never expose or resolve an inaccessible store.
- `Services\StoreDomainFinder` is the adapter behind the engine's `Misaf\VendraTenant\Contracts\HostTenantFinder` port. Host-to-store mapping lives here, never in `misaf/vendra-tenant`.
- Replace active domains through `Actions\ReplaceStoreDomainAction`: normalize and validate the replacement, retain the old domain as soft-deleted history, create the new active domain inside the store's own tenant context, and carry the storefront over — update the deployment's `domain` in the same transaction, then dispatch a forced `ProvisionStorefrontJob`. Routing lives in a container label that cannot be edited, so a domain change is a container replacement.
- Operator availability and failed provisioning recovery use `SuspendStoreAction`, `ReactivateStoreAction`, and `RetryStoreProvisioningAction`. Redeploy/retry use their domain actions and the existing deployment job/transition rules.
- Business removal uses `OffboardStoreAction`, which requires an audit reason and soft-deletes after inactivation; restoration uses `RestoreOffboardedStoreAction`, rechecks owner quota, and respects provisioning readiness. Do not expose force deletion as an operator workflow.
- Deleting a store settles its storefront in `Observers\StoreObserver`, not in the caller: soft delete records `StorefrontDesiredState::Stopped` and queues `ReconcileStorefrontJob`; force delete queues `DestroyStorefrontJob`, addressed by slug because the deployment row is cascaded away first. `Actions\DeleteStoreAction` is the transactional entry point for a programmatic delete and no longer touches the runtime itself.
- Two ownership columns, two mechanisms. Reusable domain packages own their rows through the **neutral `tenant_id`** and `Misaf\VendraSupport\Tenancy\BelongsToTenant`. Records describing the Store itself — `store_domains`, `storefront_deployments` — carry **`store_id`** and use `Concerns\BelongsToStore` / `Scopes\StoreScope`. Never add `store_id` to a reusable package's table, and never register a `store_id` table in the support `TenantTableRegistry`.
- Ownership is optional and inverted: `stores.reseller_id` is a plain nullable indexed column, the owner is fetched through `Contracts\StoreOwnerResolver`, and `ProvisionStoreAction` / `CreateStorePage::resolveOwner()` take `(Model&SubscriptionSubscriber)|null`. A null owner is a store the console owns directly, not an error.
- It depends on `misaf/laravel-docker-engine` for driver-managed Engine access and on `misaf/vendra-tenant` for the tenancy engine it plugs its `Store` into. It must **not** depend on `misaf/vendra-reseller`: a store's billing owner is reached through `Contracts\StoreOwnerResolver`, which the reseller package binds. Keep the arrow pointing reseller → store.
- Panels belong to their own packages. Put shared schemas, page bases, actions, and concerns here; put the panel-specific resource in `vendra-console` or `vendra-reseller`.

## Provisioner Contract

- `provision`, `start`, `stop`, `restart`, `destroy`, `observe`, `logs` — typed value objects on both sides, never arrays.
- Idempotence is part of the contract: provisioning twice leaves one storefront; starting a running one, stopping a stopped one, and destroying an absent one all succeed.
- `observe()` returns a `StorefrontObservation` rich enough to tell "stopped" from "running the wrong image", and must never answer "absent" for an unreachable runtime.
- `ContainerStorefrontProvisioner` builds its container through `Support\StorefrontContainerDefinitionFactory` after `Support\StorefrontConfigurationValidator` accepts the configuration. `Services\StorefrontContainerRuntime` resolves the selected Engine client through `ContainerManager`; no Docker or Podman CLI calls are allowed.

## Deployment Lifecycle

- `RequestStorefrontDeploymentAction` → `ProvisionStorefrontJob` → `StorefrontDeployment`. Reconciliation and retry go through `StorefrontDeploymentDispatchCommand`; do not add a second dispatch path.
- `CreateStorePage` skips the request only for explicit `create_storefront=false`. The console wizard may expose this through `StorefrontConfigurationFields::creationToggle()` and must use optional storefront field rules; the reseller form remains mandatory.
- `StorefrontImage` records the operator-approved immutable image reference and optional notes. New deployments may select only active catalog entries; existing deployments keep using their selected entry after it is disabled. Never reintroduce global `STOREFRONT_IMAGE` configuration.
- Write status only via `markProcessing()`, `markReady()`, `markRequested()`, `markFailed()`. `Enums\StorefrontDeploymentStatus::transitions()` is the transition table and `InvalidStorefrontTransitionException` is the rejection.
- A failing attempt with retries left stays `Processing`; only `ProvisionStorefrontJob::failed()` writes `Failed`.
- `ProvisionStorefrontJob` runs on its own `storefronts` queue, served by the single worker that holds a runtime socket. Do not move it onto a shared queue.

## Configuration

- Read infrastructure values in `config/vendra-store.php` only through the injected `Support\StorefrontSettings`. Keep it bound with `bind()` so config changes are picked up on the next resolve.
- Whether the platform is creating stores at all is `Settings\StoreCreationSettings` (group `store_creation`, `global` repository), read through `Support\StoreCreationPolicy`. Console and reseller store creation both consume the policy, so the shared rule lives below both panels.
- Runtime differences (Docker vs. a Podman compatibility socket, log driver, health-check behaviour) are configuration, never a branch. Do not add runtime sniffing.

## Testing

- Use the shared `fakeDockerEngine()` transport helper, which registers a test driver through `ContainerManager`; domain tests must not require Docker or Podman.
- Cover each lifecycle transition, including the illegal ones the enum rejects, and the retry path that must not mark a deployment `Failed` early.
- Cover the tenancy side too: Store acting as the current tenant, host resolution, `store_id` scoping of store domains, and the ownership port defaulting to `Support\NullStoreOwnerResolver`.
- Keep the architecture rules in `tests/ArchTest.php` asserting this package does not use `Misaf\VendraReseller` or `Misaf\VendraConsole`, and that actions, jobs and models stay behind the provisioning port.

## Filament

- Resources with a cluster live in `src/Filament/Clusters/Resources/`; resources without one live in `src/Filament/Resources/`. This package currently ships pages, schemas, actions, and concerns rather than resources.
