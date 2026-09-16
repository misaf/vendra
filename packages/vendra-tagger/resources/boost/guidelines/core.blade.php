## Vendra Tagger

The `misaf/vendra-tagger` package owns tagging and taxonomy and the Filament admin UI for tags.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep tagger domain code inside `packages/vendra-tagger` using the `Misaf\VendraTagger` namespace.
- Use this package for the `Tagger` model, final tag schema, factory, permission policy, Filament resource, translations, config, and package bootstrapping.
- Extend `Spatie\Tags\Tag`, preserve translated `name` and `slug` values, generate missing slugs from names, and preserve explicitly supplied slugs.
- Pin sortable behavior to `position`; the package's create migration defines it directly, so never rely on Spatie's upstream `order_column` default.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep the cluster-assigned Filament resource under `src/Filament/Clusters/Resources`, delegating forms to `Schemas/*Form.php` and tables to `Tables/*Table.php`.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraTagger\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `TaggerResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Tags`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep translated names and slugs unique per tenant and tag type; do not make the `type` field unique.
- Register the resource through `TaggerPlugin`, default it to the configured `admin` panel and shared Content navigation group, and keep host overrides working.
- Keep `config/vendra-tagger.php` cache-safe and do not add closures or a tenant toggle. Consumer applications must configure `tags.tag_model` as `Misaf\VendraTagger\Models\Tagger`.
- Bind the support-layer `TagResolver` to the Vendra `Tagger` model and Spatie pivot configuration. This is the only cross-package integration point; never import or require `misaf/vendra-product` from Tagger.
- Product integrations use the reserved `product` tag type and are enabled by capability detection in Product, not Product-specific code here.
- Other consumers reserve their own types (`user`, `blog`, and `affiliate`) and enable UI through Support capability detection. Keep all consumer-specific code out of Tagger.
- Consumer tag UI uses `Filament\Forms\Components\ModelTagsInput`, `Filament\Infolists\Components\ModelTagsEntry`, and `Filament\Tables\Columns\ModelTagsColumn` from this package (default name `tags`, shared `vendra-tagger::attributes.tags` label). Consumers still chain their own `->type(Model::TAG_TYPE)` and keep rendering them only when `TagIntegration::isAvailable()`.
- Attribute and FAQ likewise own their `attribute` and `faq` types and conditional UI; Tagger remains unaware of those domain packages.
- Publish only this package's final tag create migration. It owns optional tenant ownership, the `position` column and its ordering index, and the taggable pivot.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, the model/factory contract, sortable position, custom slug preservation, and user-visible Filament behavior.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraTagger')->not->toUse('Misaf\VendraTenant')`.
