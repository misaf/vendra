## Vendra Attribute

The `misaf/vendra-attribute` package owns reusable tenant-aware attributes and polymorphic attribute values, including their Filament administration UI.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep attribute code inside `packages/vendra-attribute` using the `Misaf\VendraAttribute` namespace.
- Keep the module independent of product or other consumers. Consumers attach values through `HasAttributeValues`; never add consumer-specific columns or relationships to this package.
- `AttributeValue` belongs to an `Attribute` and a polymorphic `attributable`; preserve that generic relationship and the sortable `position` contract.
- Resolve attribute models through the support-layer `AttributeResolver`; keep the provider binding aligned when model contracts change.
- Derive tenancy from `misaf/vendra-support` and let `BelongsToTenant` assign `tenant_id`. Never reference `Misaf\VendraTenant` or set tenant IDs manually.
- Tag-consuming models must use `Misaf\VendraSupport\Capabilities\HasOptionalTags` as the single source of their `tags()` relationship and pivot metadata. Keep the package tag-agnostic: define a stable package-owned tag type, use `TagIntegration` for availability and UI integration, never import the concrete Vendra Tagger model/provider or define the relationship through Spatie `HasTags`, and keep Tagger in Composer `suggest` rather than `require`.
- Keep unit options config-driven through `AttributeUnits`, keep Filament resources thin, and update all supported translations together.
- Because `AttributeResource` declares a `$cluster`, keep its complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraAttribute\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a cluster must instead live under `src/Filament/Resources/`.
- Keep `AttributeResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Attributes`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep architecture tests enforcing independence from Product, Tenant, Vendra Tagger, and Spatie Tags.
- Own the concrete `Misaf\VendraAttribute\Support\EloquentAttributeResolver` adapter here and bind it to Support's `AttributeResolver` contract. Preserve configurable attribute/value models, column names, active filtering, ordering, and fallback behavior. Consumers depend on the shared contract rather than this adapter.

- The resolver applies `TenantAwareness::constrainToCurrentTenant()` even when a configured model has no tenant scope. With tenancy enabled, use the resolver-defined tenant column: a current tenant sees its own active attributes and a tenantless caller sees only platform rows. Unexpected failures are reported through Laravel before falling back; empty catalogs and missing tables remain quiet.
