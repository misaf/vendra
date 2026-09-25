---
name: vendra-multimedia-development
description: "Create, modify, review, or test the Vendra Multimedia package in packages/vendra-multimedia. Use for Multimedia records, Spatie Media Library integration, reusable media concerns and conversions, polymorphic media ownership, migrations, policies, Filament resources, configuration, translations, package wiring, and the vendra-multimedia-api boundary."
---

# Vendra Multimedia

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-multimedia` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Stored media counts against the store plan's `PlanLimit::StorageMegabytesPerStore`: the provider registers the byte counter with `TenantUsageRegistry`, `ModelImageUpload::storageLimitRule()` fails validation for an oversized upload, `storageFullMessage()` shows the refusal as helper text once storage is full (the field stays enabled so images can be removed), and `MultimediaObserver::creating` refuses any other write past the limit. `MultimediaObserver::created()` reports each add with `recordAdded()`.

## Module Boundary

Treat `packages/vendra-multimedia` as the source of multimedia domain behavior and Filament admin UI.

- Use namespace `Misaf\VendraMultimedia`.
- Keep domain models, factories, seeders, policies, observers, console commands, Filament classes, config, migrations, translations, and tests inside this module.
- Do not place multimedia domain code in the host app unless the host app is only integrating the module.
- Keep API serialization and API Platform route behavior out of this module; use `vendra-multimedia-api` for that.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.

## Domain Model Standards

Follow the existing `Multimedia` patterns for new multimedia entities.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for relationships.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file's density and do not add comments that restate the code.
- Prefer only the Laravel attributes already used by the affected sibling model; do not add model attributes merely because another package uses them.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`). The module must build and run whether or not a tenant provider is installed, so never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; do not duplicate tenant scoping or `tenant_id` assignment in models, Filament resources, factories, or seeders. `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Reuse only the traits and conventions present on the affected sibling model; do not infer translations, media, slugs, sorting, or soft deletes from another package.

## Filament Standards

Keep every resource that declares a `$cluster`, including its pages, tables, and resource-scoped widgets, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraMultimedia\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`. Keep dashboard widgets in `src/Filament/Widgets`.

- Register module UI through the module `Plugin` and `ServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep `MultimediaResource` thin by delegating table configuration to `Tables/MultimediaTable.php`. Do not add a form schema unless media records intentionally become editable here.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- `Filament\Forms\Components\ModelImageUpload` (live image upload with grid panel and responsive images, full width), `Filament\Infolists\Components\ModelImageEntry` (full width), and `Filament\Tables\Columns\ModelImageColumn` (centered, stacked `thumb-table` conversion, lazy-loaded) are the shared image components for consumer packages. Keep them collection-agnostic: the media collection and default image URL are chained at the call site.
- Use this module's translation keys (`vendra-multimedia::attributes`, `vendra-multimedia::navigation`) for labels, breadcrumbs, and navigation.
- Keep `MultimediaResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Multimedia`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Prevent N+1 issues in tables and relation managers with eager loading, `withCount`, or computed state based on loaded relationships.
- Use public media visibility only when public access is actually required.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, `replicate`, and `reorder` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep navigation labels and groups configurable through the module `Plugin` and `config/vendra-multimedia.php`. Do not add a `tenant_aware` config value; tenant awareness derives from the bound `TenantResolver`.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Use package migrations in `database/migrations`, with stubs only when the install flow expects publishing.
- Keep Media Library's complete Vendra schema in one final create migration, including optional tenant ownership; do not publish Spatie's media migration separately.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-safe: import no concrete tenant provider and set no `tenant_id` directly; let `BelongsToTenant` assign it from the current tenant so they work with tenancy on or off.
- Keep demo fixtures deterministic and tenant-safe.
- Update all supported locales together and keep translation keys sorted.
- Preserve translation key parity tests when adding labels or attributes.

## Testing And Verification

Prefer focused Pest tests in the module.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Add or update unit tests for model contracts, policy permission coverage, resolver-derived tenant awareness, navigation/config behavior, and translation parity.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraMultimedia')->not->toUse('Misaf\VendraTenant')`.
- Add feature or Livewire tests when changing Filament behavior with meaningful user-visible effects.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-multimedia` and `composer stan`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent` from the host app.
