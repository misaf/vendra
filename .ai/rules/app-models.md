---
paths:
  - 'app/Models/**'
  - app/Models/StorefrontDeployment.php
---

# App Models

## Configure models with PHP attributes, not properties
Declare model configuration with PHP attributes — `#[Fillable([...])]`, `#[Hidden([...])]`, `#[ObservedBy([...])]`, `#[UseFactory(...)]` — instead of the `$fillable`, `$hidden`, `$guarded` properties.
Every model carries a full `@property` docblock covering its columns and casts.
Tenant-scoped models use the `Misaf\VendraSupport\Tenancy\BelongsToTenant` trait, which stamps, casts, hides and scopes the tenant foreign key for them — the column name comes from `vendra-tenant.foreign_key` (`tenant_id` in Vendra), never from the model.

## Change deployment status only through the model's mark* methods
Never `forceFill(['status' => ...])` on a StorefrontDeployment. Use `markProcessing()`, `markReady()`, `markRequested()`, `markFailed()` — they enforce the transition table on `StorefrontDeploymentStatus` and throw `InvalidStorefrontTransitionException` otherwise.

`Failed` is written only from `ProvisionStorefrontJob::failed()`, never from a catch inside `handle()`: an attempt that throws with retries left is still `Processing`. Marking it failed per-attempt made the panel show live deployments as dead and let `vendra-store:retry-failed` pick up rows with a job in flight.
