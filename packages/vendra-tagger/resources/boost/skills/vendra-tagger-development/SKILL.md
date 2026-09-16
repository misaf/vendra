---
name: vendra-tagger-development
description: "Create, modify, review, or test the Vendra Tagger module in packages/vendra-tagger. Use for the Tagger model, Spatie tags integration, tag migrations, factories, permission policies, Filament resources/forms/tables, translated names and slugs, sortable positions, tenant-aware validation, package configuration, plugin/service-provider wiring, translations, and module documentation."
---

# Vendra Tagger

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-tagger` and `composer stan`.

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

Treat `packages/vendra-tagger` as the source of tagger domain behavior and Filament admin UI.

- Use namespace `Misaf\VendraTagger`.
- Keep domain models, factories, seeders, policies, observers, console commands, Filament classes, config, migrations, translations, and tests inside this module.
- Do not place tagger domain code in the host app unless the host app is only integrating the module.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.
- Depend on `misaf/vendra-support`, never on a concrete tenant provider or unrelated domain package.

## Domain Model Standards

Treat `Tagger` as the package's extension of `Spatie\Tags\Tag`.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for relationships.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file's density and do not add comments that restate the code.
- Keep `#[Hidden(['tenant_id'])]` and `#[UseFactory(TaggerFactory::class)]` on the model.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`). The module must build and run whether or not a tenant provider is installed, so never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; do not duplicate tenant scoping or `tenant_id` assignment in models, Filament resources, factories, or seeders. `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Preserve Spatie's translated `name` and `slug` contract. Generate a slug from the translated name when absent and preserve a provided translated slug.
- Pin sortable behavior to the integer `position` column. Never rely on Spatie Eloquent Sortable's default `order_column`, because the package migration renames that column.
- Keep factories tenant-safe and omit `tenant_id`; tenant assignment belongs to `BelongsToTenant`.

## Filament Standards

Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraTagger\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.

- Register module UI through the module `Plugin` and `ServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep resource classes thin. Delegate form schemas to `Schemas/*Form.php` and table configuration to `Tables/*Table.php`.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use this module's translation keys (`vendra-tagger::attributes`, `vendra-tagger::navigation`) for labels, breadcrumbs, and navigation.
- Keep `TaggerResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Tags`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep translated name and slug validation unique within the current tenant and tag type. The `type` field itself is not unique.
- Keep table reordering on `position` and use the permission-backed `reorder` policy.
- `Filament\Forms\Components\ModelTagsInput` (live, validates its own state path, full width), `Filament\Infolists\Components\ModelTagsEntry` (full width), and `Filament\Tables\Columns\ModelTagsColumn` (toggleable) are the shared tag components for consumer packages. Keep them type-agnostic: the tag type is always chained at the call site.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, `replicate`, and `reorder` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep navigation labels and groups configurable through the module `Plugin` and `config/vendra-tagger.php`. Do not add a `tenant_aware` config value; tenant awareness derives from the bound `TenantResolver`.
- Default `panels` to `['admin']` and `navigation_group` to `vendra-support::navigation.groups.Content`; allow plugin-level navigation group overrides.

## Package Integration

- Register config, translations, migrations, and commands through `TaggerServiceProvider` using Laravel Package Tools.
- Register Filament resources through `TaggerPlugin`; do not wire them into host panel providers.
- Keep `config/vendra-tagger.php` cache-safe: use lowercase keys, scalar/array values, no closures, and `env()` only when an environment override is genuinely needed.
- Require consumers to point `tags.tag_model` at `Misaf\VendraTagger\Models\Tagger`. Do not silently replace an explicit host override.
- Bind `Misaf\VendraSupport\Contracts\TagResolver` to relationship metadata for `Tagger` and the configured Spatie taggable pivot. This lets consumers detect tags without importing this package.
- Never depend on or import `misaf/vendra-product`. Product-specific UI and the reserved `product` type belong to Product; Tagger remains a generic provider.
- The User, Blog, and Affiliate packages likewise own their `user`, `blog`, and `affiliate` types and UI. Never add those domain packages as Tagger dependencies.
- Attribute and FAQ own their `attribute` and `faq` types and UI. Never add those packages as Tagger dependencies.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Publish only the package's final tag create migration. It directly defines optional tenant ownership, `position`, tenant-aware ordering indexes, and the taggable pivot; do not publish Spatie's migration separately.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-safe: import no concrete tenant provider and set no `tenant_id` directly; let `BelongsToTenant` assign it from the current tenant so they work with tenancy on or off.
- Update all supported locales together and keep translation keys sorted.
- Preserve translation key parity tests when adding labels or attributes.

## Testing And Verification

Prefer focused Pest tests in the module.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Add or update unit tests for the model/factory contract, sortable column, custom slug preservation, permission coverage, navigation/config behavior, and translation parity.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraTagger')->not->toUse('Misaf\VendraTenant')`.
- Add feature or Livewire tests when changing Filament behavior with meaningful user-visible effects.
- Run the suite from the host app: `php artisan test --compact --testsuite=vendra-tagger`. Use the host-level PHPStan command for analysis.
- Validate package configuration with the root `tests/Feature/PackageConfigurationTest.php` and confirm `php artisan config:cache` succeeds after config changes.
- If PHP files changed, run Pint for only the touched files when the worktree contains unrelated changes.
