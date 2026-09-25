## Vendra Multimedia

The `misaf/vendra-multimedia` package owns multimedia and media library management (Spatie Media Library) and the Filament admin UI for multimedia/media records.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Stored media counts against the store plan's `PlanLimit::StorageMegabytesPerStore`: the provider registers the byte counter with `TenantUsageRegistry`, `ModelImageUpload::storageLimitRule()` fails validation for an oversized upload, `storageFullMessage()` shows the refusal as helper text once storage is full (the field stays enabled so images can be removed), and `MultimediaObserver::creating` refuses any other write past the limit. `MultimediaObserver::created()` reports each add with `recordAdded()`.

- Keep multimedia domain code inside `packages/vendra-multimedia` using the `Misaf\VendraMultimedia` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, observers, Filament resources, translations, config, and package bootstrapping.
- Keep the final Media Library schema in this package's single create migration, including optional tenant ownership. Do not publish Spatie's media migration separately.
- This package is the ecosystem's media platform and the sole carrier of `spatie/laravel-medialibrary`; that dependency is part of this package's public contract, not an implementation detail. Consumer modules require `misaf/vendra-multimedia` — never `spatie/laravel-medialibrary` directly — and may import Spatie media classes (`HasMedia`, `InteractsWithMedia`, `Media`) and Filament `SpatieMediaLibrary*` components on that basis.
- Never drop, swap, or major-bump `spatie/laravel-medialibrary` in a minor release of this package: a medialibrary major version bump is a multimedia major version bump. The consumer rule is enforced by the root `tests/Feature/PackageManifestConsistencyTest.php`.
- Consumer image UI uses `Filament\Forms\Components\ModelImageUpload`, `Filament\Infolists\Components\ModelImageEntry`, and `Filament\Tables\Columns\ModelImageColumn` from this package (default name `image`, shared `vendra-multimedia::attributes.image` label). Consumers chain `->collection(Model::MEDIA_COLLECTION)`, and the column's `->defaultImageUrl()`, at the call site.
- Keep API Platform serialization and API routes in `misaf/vendra-multimedia-api`.
- Follow the concrete models and neighboring files in this package; do not apply translation, media, slug, sorting, or soft-delete patterns unless the affected model already uses them.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep the read-only Filament resource under `src/Filament/Clusters/Resources`, delegate table configuration to `Tables/MultimediaTable.php`, and preserve its shared `ContentCluster` assignment. Do not add a form schema unless media records intentionally become editable here.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraMultimedia\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `MultimediaResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Multimedia`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, model contracts, and user-visible Filament behavior.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraMultimedia')->not->toUse('Misaf\VendraTenant')`.
