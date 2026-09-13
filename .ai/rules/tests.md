---
paths:
  - 'packages/*/tests/**'
---

# Tests

## Pest tests use it(), bound to the host TestCase
Write every test with `it('...')` — `test()` is not used anywhere. Group related cases with `describe()` when a file grows.
Package tests are bound to `Tests\TestCase` by the host's `tests/Pest.php`, and `phpunit.xml` registers one suite per package, so run them from the project root: `php artisan test --compact --testsuite=vendra-<name>`.

## Tenant-agnostic test rule only binds provider-agnostic packages
Package tests must not import `Misaf\VendraTenant\*` — use the vendra-testing helpers (`createTestTenant()`, `makeCurrentTestTenant()`, `testTenantModel()`, `createTestUser()`, `testUserModel()`, `vendraTestingModelFactory()`) instead. `PackageManifestConsistencyTest` enforces this, but skips `vendra-tenant` and any package whose own `src/` already imports the tenant provider (store, reseller, console): a test suite cannot be looser about the provider than the code it covers. The sibling rule still applies everywhere — a package must directly `require` every Vendra module its tests import, transitive deps do not count.

## Keep package unit tests framework-free
Package tests under tests/Unit use PHPUnit's default TestCase. Move tests that need Laravel, Testbench, config, facades, Eloquent, translations, Filament/Livewire, or the database to tests/Feature; the host tests/Pest.php binds Tests\TestCase only to ../packages/*/tests/Feature.

## Pest tests use it(), bound to the host TestCase
Write every test with it(); group related cases with describe(). Only package Feature paths are bound to the host Tests\TestCase; package Unit paths use Pest's default PHPUnit TestCase. phpunit.xml registers each package suite for root-level execution.

## Feature-only Laravel binding is authoritative
Treat the feature-only convention as authoritative: only package tests/Feature paths use the host Tests\TestCase. Package tests/Unit paths always use Pest's default PHPUnit TestCase.
