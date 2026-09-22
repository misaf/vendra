# Vendra Platform — Project Context

> Shared agent context for this repository. `CLAUDE.md`, `AGENTS.md`, and other
> agent instruction files point here so there is one copy to maintain. The generated
> `<laravel-boost-guidelines>` blocks in those files are refreshed by
> `php artisan boost:update --no-interaction`; this file is hand-written and is not.

## Commands

```bash
composer setup                 # install deps, .env, key, migrate, npm build
composer dev                   # serve + queue:listen + pail + vite (concurrently)
composer test                  # config:clear then pest --parallel --tia
composer stan                  # phpstan (larastan), 1G memory limit
npm run dev | npm run build    # Vite only
```

Testing (Pest 5). `phpunit.xml` registers **one test suite per package** plus host
`Unit`/`Feature`, so host-level commands can run package tests too:

```bash
php artisan test --parallel --compact
php artisan test --parallel --compact --filter=testName
php artisan test --compact --testsuite=vendra-product
php artisan test --compact packages/vendra-product/tests/Feature/SomeTest.php
```

Drop `--parallel` only when debugging a failure, chasing a race, or when shared
mutable/external state cannot be isolated.

Formatting: `vendor/bin/pint --dirty --format agent` before finalizing PHP changes.

Package manifests deliberately contain no development dependencies or scripts.
Run package tests, analysis, and formatting through the host-level commands above.

## Architecture

**Composer monorepo.** The host app (`app/`) plus ~40 first-party packages in
`packages/vendra-*`, wired as path repositories with `"self.version"` constraints.
Releases are tagged across all packages by `monorepo-builder.php` (default branch
`1.x`). Package namespace convention: `Misaf\Vendra<Name>\` → `packages/vendra-<name>/src`.

Package layering:
- `vendra-support` holds provider-neutral **contracts** (`TenantResolver`,
  `BelongsToTenant`, `TenantScope`, `TenantSchema`); every domain package depends on
  it and on nothing tenant-specific.
- `vendra-tenant` is the **generic tenancy engine**: it activates tenancy by binding
  `ConfiguredTenantResolver`, and it names no business model. Domain and API packages
  must never depend on it directly.
- `vendra-store` supplies the **concrete tenant**: `Misaf\VendraStore\Models\Store`
  implements `Misaf\VendraTenant\Contracts\TenantContract`, so Store *is* the tenant
  — there is no separate `tenants` table. It also owns store domains and the
  storefront lifecycle. `vendra-reseller` sits above it and `vendra-console` above
  both; the arrows never point back down.
- `vendra-<domain>-api` packages expose the corresponding domain package over API
  Platform; keep domain logic out of them.
- `vendra-testing` supplies shared Testbench/Pest scaffolding for package tests.

**Three Filament panels**, each a provider in `app/Providers/Filament/` with its own
`app/Filament/<Panel>/` discovery root:
- `admin` — default panel, root path on the tenant domain, runs *inside* the tenant
  middleware stack (`NeedsTenant`, `EnsureValidTenantSession`, `EnsureAdminDomain`).
- `console` — `vendra-console.domain` (defaults to `console.<app host>`), console users
  managing resellers/plans/stores across all tenants; runs **outside** tenant middleware.
- `reseller` — `vendra-reseller.domain` (defaults to `reseller.<app host>`); a reseller
  spans multiple stores, so it also runs **outside** tenant middleware.

When adding a resource, put it under the panel directory whose tenancy scope matches
the data, and check whether the panel is tenant-aware before assuming a current tenant.

**Tenancy** is `spatie/laravel-multitenancy` configured in `config/multitenancy.php`:
domain-based `StoreDomainFinder`, tenant model `Misaf\VendraStore\Models\Store`,
switch tasks prefix the cache, swap route cache, app config, and mail config. Queued
jobs are tenant-aware by default — mark cross-tenant jobs `NotTenantAware`.

**Storefront provisioning.** Laravel owns the business state and the storefront
containers. `Misaf\VendraStore\Contracts\StorefrontProvisioner` is a typed port —
`StorefrontProvisionRequest` in, `StorefrontProvisionResult` out — with one
implementation, `Misaf\VendraStore\Services\ContainerStorefrontProvisioner`: it
talks to `misaf/laravel-docker-engine` through the injected
`Misaf\VendraStore\Services\StorefrontContainerRuntime` adapter, creating one
container per storefront from `StorefrontContainerDefinitionFactory` after
`StorefrontConfigurationValidator` validates it. There is no external provisioner.

Only Engine-API calls are used, so Docker and a Podman compatibility socket both
serve it. Laravel selects `container.default` through `CONTAINER_DRIVER`; Docker
uses `DOCKER_HOST`, and Podman uses `PODMAN_HOST`. The estate's host-side script
still selects its Compose CLI with `CONTAINER_RUNTIME` and mounts
`CONTAINER_SOCKET` into the storefront worker. Runtime differences are
configuration, never a branch: the log driver is `STOREFRONT_LOG_DRIVER` (empty
omits the block), and a runtime that does not execute health checks makes the
health gate degrade to "running" with a logged warning. Do not add runtime
sniffing.

The flow is `RequestStorefrontDeploymentAction` → `ProvisionStorefrontJob` →
`StorefrontDeployment`, with reconciliation and retry sharing
`StorefrontDeploymentDispatchCommand`. Infrastructure settings are read through
the injected `StorefrontSettings` value object; the runtime availability check is
owned by `StorefrontRuntimeConfiguration`.

Storefront images and their built-in themes are dynamic business data, not
environment configuration. Console users manage the `StorefrontImage` catalog,
and every deployment references its selected catalog row. New deployments may use
only active images; existing deployments keep their selected image if it is later
disabled. Provisioning and reconciliation never read global `STOREFRONT_IMAGE` or
`STOREFRONT_THEMES` values.

Status changes go through the model's `markProcessing()`/`markReady()`/
`markRequested()`/`markFailed()`, which enforce `StorefrontDeploymentStatus`'
transition table. A job attempt that throws with retries left stays `Processing`:
only `ProvisionStorefrontJob::failed()` writes `Failed`.

A storefront is one container carrying the `traefik.*` labels the edge proxy
discovers, plus `io.vendra.*` labels marking it platform-owned — the platform never
replaces or removes a container it did not place. There is no Compose project per
store. The provisioner does **not** create the network: it fails with a pointed
error when the network is missing rather than improvising one the proxy is not
attached to.

`ProvisionStorefrontJob` runs on its own `storefronts` queue
(`ProvisionStorefrontJob::QUEUE`), served by the `storefront-worker` container — the
only container in the estate holding a runtime socket, which is root-equivalent on the
host under Docker (not under rootless Podman). Horizon's supervisor deliberately does not list that queue. Do not widen the
worker's queues or give Horizon the socket.

**The estate lives in `docker/stacks/`** — Traefik, the platform services, and the
optional marketing site, driven by the host-level `docker/stacks/bin/vendra` script
(the platform runs inside the stack, so it cannot bootstrap itself). This repo is the
source of truth for those Compose assets. Traefik owns TLS: ACME in production, mkcert locally via `vendra certs`.
Nothing in this repo generates a CA. See `docker/stacks/README.md`.

**API.** Package-owned API Platform resources are exposed under
`/api/{admin-navigation-group}/{model-resource}` so the public API mirrors the Filament
admin structure (e.g. `/api/catalog/products`); OpenAPI at `/api/docs`. Frontend code
uses the `window.api` fetch client from `resources/js/bootstrap.js`.

## Package documentation contract

Every package keeps the same set aligned with its `composer.json`, source, and tests —
update all affected layers in one change:
- `README.md` — user-facing contract (features, requirements, install, usage, checks).
- `resources/boost/guidelines/core.blade.php` — always-loaded boundaries and invariants.
- `resources/boost/skills/*/SKILL.md` — on-demand workflow guidance.

Describe optional integrations as optional; never document unimplemented behavior.

## Troubleshooting

- Package changes not picked up → `composer dump-autoload`
- Stale provider discovery → `php artisan package:discover`
- Stale config → `php artisan config:clear`
