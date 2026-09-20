---
paths:
  - 'packages/*/src/**'
---

# Src

## Classes are final; no controller layer
Declare new classes `final` — the codebase is `final class` almost throughout, with `abstract` reserved for the handful of deliberate base classes.
There is no controller layer. The HTTP surface is Filament panels plus API Platform resources (`src/ApiResource/`, `src/State/`); business operations live in `src/Actions/`. Only add an `Http/Controllers/` class for a genuine standalone endpoint (redirect, webhook, callback), and make it a single-action `__invoke()` controller.

## Create users and change passwords through vendra-user actions
Never create a `User` with `User::query()->create()` or hash a user password with `Hash::make` outside `vendra-user`. Use `Misaf\VendraUser\Actions\CreateUserAction` — pass `tenant: null` for tenantless identities (console, reseller), which keeps `tenant_id` null even inside a tenant context — and `UpdateUserPasswordAction`, which handles tenant-less users. Do not add per-package copies (e.g. the removed `UpdateResellerUserPasswordAction`).
