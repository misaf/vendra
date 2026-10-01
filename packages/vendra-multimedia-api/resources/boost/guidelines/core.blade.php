## Vendra Multimedia API

The `misaf/vendra-multimedia-api` package exposes `misaf/vendra-multimedia` domain models through API Platform for Laravel.

Public multimedia responses include a `url`. Select `tenant_id` for media URL generation. Local public-disk URLs are relative to the API host; clients on another host must resolve them against the API origin.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Keep API code inside `packages/vendra-multimedia-api` using the `Misaf\VendraMultimediaApi` namespace.
- Use this package for API Platform resources (`ApiResource` DTOs), state providers, query parameters, service providers, and API tests.
- Import domain models from `Misaf\VendraMultimedia`; do not duplicate persistence or domain behavior in the API module.
- Keep Filament/admin UI in `misaf/vendra-multimedia`.
- Respect domain model tenancy. Tenant awareness is owned by `misaf/vendra-support` and derives from the bound `TenantResolver` (installing `misaf/vendra-tenant` enables it); there is no `tenant_aware` config toggle.
- Keep production API code tenant-provider agnostic: inherit tenancy through the `Misaf\VendraMultimedia` models and never reference `Misaf\VendraTenant` in resources, state providers, or query parameters. Feature tests may use a concrete tenant factory solely to establish tenant context; architecture expectations remain scoped to the production `Misaf\VendraMultimediaApi` namespace.
- Declare query parameters on the resource operations and apply them in the state provider.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for each resource operation, query parameters, and pagination.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraMultimediaApi')->not->toUse('Misaf\VendraTenant')`.
