---
paths:
  - 'packages/vendra-tenant/**'
---

# Vendra Tenant

## The engine names no business model
`misaf/vendra-tenant` is a generic tenancy mechanism. It must not import `Misaf\VendraStore`, `Misaf\VendraReseller`, `Misaf\VendraConsole`, or `Misaf\VendraSubscription` — in source, config defaults, or tests. Its own suite runs against a `tests/Fixtures/Workspace` model with a `workspace_id` foreign key, so a business assumption leaking in fails a test rather than passing quietly.

## Business shape is configuration, read through TenantSchema
`vendra-tenant.model` (a class implementing `Contracts\TenantContract`), `vendra-tenant.foreign_key`, and `vendra-tenant.relation` (an optional business alias such as `store`). Read them via `TenantSchema::column()` / `TenantSchema::relationName()` or the bound `TenantResolver`; never hard-code `tenant_id`. Vendra keeps `foreign_key` as `tenant_id` deliberately so reusable domain packages stay portable.

## Ports get bindIf, adapters get bind
`HostTenantFinder` (host → tenant) is a port this package only defaults with `NullHostTenantFinder` via `bindIf`. `misaf/vendra-store` binds `StoreDomainFinder` over it. Provider discovery order is alphabetical, so a plain `bind` on the default would beat the real adapter.

## Adding a TenantResolver method is a cross-package change
The contract lives in `misaf/vendra-support`. Changing it means updating `ConfiguredTenantResolver`, `NullTenantResolver`, **and** the `mock(TenantResolver::class)` setups in vendra-language, vendra-currency, vendra-blog and vendra-newsletter — Mockery throws on an unexpected call during model boot, which cascades into `bootIfNotBooted` errors across the whole process.
