---
name: vendra-permission-development
description: "Create, modify, review, or test the Vendra Permission package in packages/vendra-permission. Use for Role, Permission, Spatie Permission integration, role creation actions, guards and teams, policy enums and seeders, migrations, Filament resources and relation managers, configuration, translations, package wiring, and authorization tests."
---

# Vendra Permission

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-permission` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

## Module Boundary

Treat `packages/vendra-permission` as the source of permission domain behavior and Filament admin UI.

- Use namespace `Misaf\VendraPermission`.
- Keep domain models, factories, seeders, policies, observers, console commands, Filament classes, config, migrations, translations, and tests inside this module.
- Do not place permission domain code in the host app unless the host app is only integrating the module.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.

## Domain Model Standards

Follow the existing `Permission` and `Role` patterns for new permission entities.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for relationships.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file's density and do not add comments that restate the code.
- Prefer only the Laravel attributes already used by the affected sibling model; do not add model attributes merely because another package uses them.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`). The module must build and run whether or not a tenant provider is installed, so never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; do not duplicate tenant scoping or `tenant_id` assignment in models, Filament resources, factories, or seeders. `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Reuse only the traits and conventions present on the affected sibling model; do not infer translations, media, slugs, sorting, or soft deletes from another package.
- `Permission` and `Role` extend Spatie Permission models; the final package create migration conditionally includes `tenant_id` through `TenantSchema` and includes Vendra descriptions. Pennant features are scoped through the resolver's model class, never a concrete `Tenant`.

## Filament Standards

Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraPermission\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.

- Register module UI through the module `Plugin` and `ServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep resource classes thin. Delegate form schemas to `Schemas/*Form.php` and table configuration to `Tables/*Table.php`.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use this module's translation keys (`vendra-permission::attributes`, `vendra-permission::navigation`) for labels, breadcrumbs, and navigation.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Prevent N+1 issues in tables and relation managers with eager loading, `withCount`, or computed state based on loaded relationships.
- Use public media visibility only when public access is actually required.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, `replicate`, and `reorder` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep navigation labels and groups configurable through the module `Plugin` and `config/vendra-permission.php`. Do not add a `tenant_aware` config value; tenant awareness derives from the bound `TenantResolver`.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Use package migrations in `database/migrations`, with stubs only when the install flow expects publishing.
- Publish only the package's final permission create migration; do not publish Spatie's base migration and Vendra follow-up migrations together.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-safe: import no concrete tenant provider and set no `tenant_id` directly; let `BelongsToTenant` assign it from the current tenant so they work with tenancy on or off.
- Keep demo fixtures deterministic and tenant-safe.
- Update all supported locales together and keep translation keys sorted.
- Preserve translation key parity tests when adding labels or attributes.

## Testing And Verification

Prefer focused Pest tests in the module.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Add or update unit tests for model contracts, policy permission coverage, resolver-derived tenant awareness, navigation/config behavior, and translation parity.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraPermission')->not->toUse('Misaf\VendraTenant')`.
- Add feature or Livewire tests when changing Filament behavior with meaningful user-visible effects.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-permission` and `composer stan`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent` from the host app.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.
