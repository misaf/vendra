---
paths:
  - 'packages/*/composer.json'
  - 'packages/**'
---

# Packages

## Centralize development dependencies at the host root
Package tests, static analysis, and formatting always run from the monorepo host. Keep all development dependencies in the root composer.json; package composer.json manifests must not define require-dev dependencies.

## Keep package Composer manifests runtime-only
The monorepo host owns tests, static analysis, and formatting. Package composer.json files must contain neither require-dev nor scripts; use root Composer and Artisan commands for every package check.

## Require PHP 8.4 across all packages
The host and every first-party package target PHP ^8.4. Keep package composer constraints, root lock metadata, and README requirements aligned when changing the PHP baseline.

## Centralize package test autoloading at the host
Child package manifests do not declare *\Tests\ PSR-4 entries. The monorepo root composer.json owns package test namespace mappings because Composer ignores dependencies' autoload-dev; child manifests keep only database factory autoload-dev mappings when a factory directory exists.

## One vocabulary: user, reseller, administrator — never "operator"
Identities are always `user`: the canonical `Misaf\VendraUser\Models\User` plus per-panel grant tables `console_users` and `reseller_users`. "Operator" is not a word this codebase uses (the only exceptions are Filament's `IsRelatedToOperator` and Rector's `OptionalToNullsafeOperatorRector`).

The billing entity behind `stores.reseller_id` is a `reseller`, never an "owner": `AssignStoreResellerAction`, `Contracts\StoreResellerResolver`, `CreateStorePage::resolveReseller()`, `?SubscriptionSubscriber $reseller = null`.

A store's first tenant-level account is an `administrator` (`console.administrator_credentials`, `RoleEnum::Admin`), distinct from the reseller's `user`.

`owner` survives only where it is someone else's API: `cache_locks.owner`, the cart/wishlist `owner` morph, and Filament's `$ownerRecord`.
