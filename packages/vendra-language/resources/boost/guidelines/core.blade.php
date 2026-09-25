## Vendra Language

The `misaf/vendra-language` package owns multi-language support with store and tenantless platform languages, database translation lines, and shared Filament UI components for Admin and Console resources.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep language domain code inside `packages/vendra-language` using the `Misaf\VendraLanguage` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, observers, Filament resources, translations, config, and package bootstrapping.
- Keep the fresh-install create migration for `languages` and `language_lines` as the schema baseline. Tenant IDs are nullable, with separate uniqueness guards for tenantless language and translation rows.
- Follow the slim `Language` model conventions: tenant ownership via `BelongsToTenant`, an ICU-validated `locale` tag (web/BCP-47), a boolean `active`, an `is_default` flag, and a sortable integer `position`. There is no stored name, slug, description, or media — display names derive from the ICU catalog through `Misaf\VendraLanguage\Support\Locales` (`symfony/intl`). A row is installed; `active` determines whether it participates in language switches, translation locale lists, and tenant locale resolution.
- Keep package translation synchronization centralized in `TranslationCatalog` and `SyncLanguageLinesAction`. Discover only registered `vendra-*` translation namespaces from the Laravel file loader, flatten nested PHP translation arrays to dot-notation keys, and collect every locale provided by each installed package.
- Treat package files as import defaults and database language lines as user-owned overrides. Synchronization may create missing lines and add locale keys that are absent from an existing line, but it must never replace an existing database locale value, including an intentionally blank value. Keep synchronization idempotent, tenant-scoped through `BelongsToTenant`, and transaction-safe.
- Expose synchronization from both Languages and Language Lines list pages through the shared Filament action. Because it can create and update records, require both Language Line create and update authorization and report created, updated, and unchanged counts.
- When `misaf/vendra-localization` is installed, the service provider bridges the catalog into it: it feeds `supported_locales` and appends `TenantLocaleResolver` (the tenant default, lowest priority) to the resolver chain. Keep this optional and guarded — never hard-require localization, and never make `vendra-tenant` depend on this module.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep Filament resources thin by delegating forms to `Schemas/*Form.php` and tables to `Tables/*Table.php`.
- Because the package resources declare a `$cluster`, keep their complete resource trees under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraLanguage\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a cluster must instead live under `src/Filament/Resources/`.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, model contracts, and user-visible Filament behavior.
- Cover translation synchronization with focused tests for nested keys, all discovered package locales, preservation of customized and blank database values, missing-locale merging, idempotency, and action availability.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraLanguage')->not->toUse('Misaf\VendraTenant')`.
