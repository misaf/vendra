## Vendra Activity Log

The `misaf/vendra-activity-log` package owns activity/audit logging and the Filament admin UI for activity log records.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep activity-log domain code inside `packages/vendra-activity-log` using the `Misaf\VendraActivityLog` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, observers, Filament resources, translations, config, and package bootstrapping.
- Keep the final Activity Log schema in this package's single create migration, including event, batch UUID, and optional tenant ownership. Do not publish Spatie's base and follow-up migrations alongside it.
- `Listeners\LogSettingsActivity` listens to `SavingSettings` and logs a settings class that implements `ShouldLogActivity`: only the properties the save changes, with the stored values read from a fresh instance as `old`, under log name `settings` and the settings group as description, with no subject. A save without a current store writes the platform row and is logged as platform activity. Keep platform (global repository) settings classes off `ShouldLogActivity` for now: saved while a store is current, `BelongsToTenant` would stamp that store on their entry.
- `activity_log.tenant_id` is nullable and a null is platform activity, so `activity_log` stays out of `TenantTableRegistry` (the `vendra-tenant:enable` retrofit would backfill it and force NOT NULL). `LogModelActivity` records the current store when there is one; without one it records the store that owns the subject (the subject's tenant id, or the key of a tenant model itself) and leaves a tenantless subject as platform activity, so logging never fails the write in the console or reseller panels. The admin panel's tenant scope hides platform activity from stores.
- Follow the concrete models and neighboring files in this package; do not apply translation, media, slug, sorting, or soft-delete patterns unless the affected model already uses them.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep the read-only Filament resource under `src/Filament/Clusters/Resources`, delegate table configuration to `Tables/ActivityLogTable.php`, and preserve its shared `SystemCluster` assignment. Do not add a form schema unless activity records intentionally become editable.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraActivityLog\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` case; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, model contracts, and user-visible Filament behavior.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraActivityLog')->not->toUse('Misaf\VendraTenant')`.
