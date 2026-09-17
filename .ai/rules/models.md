---
paths:
  - 'packages/*/src/Models/**'
---

# Models

## Configure models with PHP attributes, not properties
Declare model configuration with PHP attributes — `#[Fillable([...])]`, `#[Hidden([...])]`, `#[ObservedBy([...])]`, `#[UseFactory(...)]` — instead of the `$fillable`, `$hidden`, `$guarded` properties.
Every model carries a full `@property` docblock covering its columns and casts.
Tenant-scoped models use the `Misaf\VendraSupport\Tenancy\BelongsToTenant` trait, which stamps, casts, hides and scopes the tenant foreign key for them — the column name comes from `vendra-tenant.foreign_key` (`tenant_id` in Vendra), never from the model. Store-owned records are the exception: they carry `store_id` instead. `StoreDomain` also uses `Misaf\VendraStore\Concerns\BelongsToStore`, which adds a global `StoreScope`. `StorefrontDeployment` deliberately does not — the fleet-wide commands (`vendra-store:reconcile`, `:redeploy`, `:retry-failed`, `:status`) sweep every store's deployments, and a global store scope would hide all but the current one.

## Lifecycle logic lives in an observer; `booted()` is only for self-attribute defaults
A `booted()` closure may set a default on the model being saved — a token, an expiry — and nothing else. Anything that writes another record, enforces an invariant by throwing, cascades a delete, or needs a collaborator goes in an `Observers/` class wired with `#[ObservedBy([...])]`, where it is testable, discoverable from the model's attributes, and able to take constructor injection.

Observer methods a model's own logic drives must be `public`: a private method is reachable from a same-class `booted()` closure but not from an observer.

Pick queued vs synchronous deliberately, and say which in the class docblock:
- **Synchronous** (plain class) is required for `creating`/`saving` (they mutate before the write), for guards that throw to abort, for `forceDeleting` (the related rows are about to disappear), and for anything reading `wasChanged()` or `isForceDeleting()` — that state does not survive serialization.
- **Queued** (`implements ShouldQueue`, `$afterCommit = true`) suits after-the-fact cleanup such as a `deleted` cascade.

When a model needs both, register two observers rather than forcing one to be synchronous — see `Product`, which pairs the queued `ProductObserver` with the synchronous `ProductLifecycleObserver`.
