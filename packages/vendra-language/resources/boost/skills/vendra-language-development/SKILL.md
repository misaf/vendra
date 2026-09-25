---
name: vendra-language-development
description: "Create, modify, review, or test the Vendra Language package in packages/vendra-language. Use for Language, LanguageLine, the ICU locale catalog, default-language behavior, database translations, TenantLocaleResolver integration, actions, observers, migrations, policies, Filament resources, configuration, translations, and package wiring."
---

# Vendra Language

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-language` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Platform language and translation rows have a null tenant ID. Scope shared form options, progress, translation loading, and synchronization to the current tenant or tenantless platform rows, and preserve the platform uniqueness guards in migrations.

## Module Boundary

Treat `packages/vendra-language` as the source of language domain behavior and Filament admin UI.

- Use namespace `Misaf\VendraLanguage`.
- Keep domain models, factories, seeders, policies, observers, console commands, Filament classes, config, migrations, translations, and tests inside this module.
- Do not place language domain code in the host app unless the host app is only integrating the module.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.

## Domain Model Standards

Follow the existing `Language` and `LanguageLine` patterns for new language entities.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for relationships.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file's density and do not add comments that restate the code.
- Prefer the Laravel attributes already used here, such as `#[Fillable]`, `#[Hidden]`, `#[UseFactory]`, and `#[ObservedBy]`.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`). The module must build and run whether or not a tenant provider is installed, so never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; do not duplicate tenant scoping or `tenant_id` assignment in models, Filament resources, factories, or seeders. `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Keep locale records slim: `Language` persists only `locale`, `active`, `is_default`, and `position`. Derive display names from the ICU catalog via `Misaf\VendraLanguage\Support\Locales` (`symfony/intl`) — do not store or translate `name`/`description`/`slug` columns on a locale record.
- Store web/BCP-47 locale tags (`en`, `pt-BR`) and validate them against the ICU catalog (`Locales::isSupported()`). Every ICU locale is installable, regardless of the preferred locale subset in `config('vendra-language.locales')`.
- Treat a `Language` row's presence as "installed" for the tenant and its boolean `active` as "enabled". Disabled languages remain manageable but must be excluded from language switches, translation locale lists, and tenant locale resolution.
- Use `SortableTrait` and an integer `position` field for ordered admin content.
- Reserve `SoftDeletes`, media (`HasMedia` + `InteractsWithMedia` + `HasDefaultMediaConversions` + a `multimedia()` morph relation), and slugs (`Spatie\Sluggable\SlugOptions`) for richer content entities that genuinely need them — `Language` uses none of these, and language flags come from the locale code, not stored media.

## Filament Standards

Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraLanguage\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.

- Register module UI through the module `Plugin` and `ServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep resource classes thin. Delegate form schemas to `Schemas/*Form.php` and table configuration to `Tables/*Table.php`.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use this module's translation keys (`vendra-language::attributes`, `vendra-language::navigation`) for labels, breadcrumbs, and navigation.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Prevent N+1 issues in tables and relation managers with eager loading, `withCount`, or computed state based on loaded relationships.
- Use public media visibility only when public access is actually required.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, `replicate`, and `reorder` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep navigation labels and groups configurable through the module `Plugin` and `config/vendra-language.php`. Do not add a `tenant_aware` config value; tenant awareness derives from the bound `TenantResolver`.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Keep installed-package translation discovery in `TranslationCatalog`. Read only registered `vendra-*` namespaces from Laravel's `FileLoader`, flatten nested PHP translation arrays to dot-notation keys, and retain every locale found in package files.
- Use `SyncLanguageLinesAction` as the synchronization boundary. Package files provide import defaults; database `LanguageLine` values are user-owned overrides. Create missing lines and add only locale keys absent from existing rows. Never replace an existing database locale value, including an intentionally blank value.
- Keep synchronization idempotent and transaction-safe. Load existing tenant-scoped lines in a batch, let `BelongsToTenant` assign ownership, and return created, updated, and unchanged counts for user feedback.
- Keep the shared synchronization action available from both Languages and Language Lines list pages. Since synchronization can create and update rows, authorize both Language Line create and update abilities.
- Use package migrations in `database/migrations`, with stubs only when the install flow expects publishing.
- Keep fresh installs to the create migration for `languages` and `language_lines`. Define tenantless platform uniqueness there; do not include data backfills in the fresh baseline.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-safe: import no concrete tenant provider and set no `tenant_id` directly; let `BelongsToTenant` assign it from the current tenant so they work with tenancy on or off.
- Keep demo fixtures deterministic and tenant-safe.
- Update all supported locales together and keep translation keys sorted.
- Preserve translation key parity tests when adding labels or attributes.
- The installable locale catalog comes from Symfony Intl. `config('vendra-language.locales')` is only the host's preferred subset for integrations that need static defaults. Fall back to `config('app.fallback_locale')` (never a duplicate module default) when a tenant has no active languages.
- When `misaf/vendra-localization` is installed, the provider feeds its `supported_locales` from the catalog and appends `TenantLocaleResolver` (tenant default, lowest priority) to the resolver chain. Keep this optional and guarded (`misaf/vendra-localization` is a `suggest`, not a `require`); never make `vendra-tenant` depend on this module.

## Testing And Verification

Prefer focused Pest tests in the module.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Add or update unit tests for model contracts, policy permission coverage, resolver-derived tenant awareness, navigation/config behavior, and translation parity.
- Test translation synchronization for nested keys, all package-provided locales, preservation of customized and blank database values, missing-locale merging, idempotency, and Filament action availability.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraLanguage')->not->toUse('Misaf\VendraTenant')`.
- Add feature or Livewire tests when changing Filament behavior with meaningful user-visible effects.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-language` and `composer stan`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent` from the host app.
