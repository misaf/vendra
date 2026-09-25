## Vendra Cart

The `misaf/vendra-cart` package owns temporary shopping selections before checkout and the Filament administration UI for inspecting carts and their items.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (how many days a new cart lives, or null for no expiry) live in `Settings\CartSettings` (group `cart`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageCartSettings` edits them in the admin System cluster; read them with `resolve(CartSettings::class)`, never from config.

- Keep cart domain code inside `packages/vendra-cart` using the `Misaf\VendraCart` namespace.
- `Cart` owns lifecycle data such as its token, optional polymorphic owner, expiration, and items. `CartItem` stores quantity and optional selection metadata.
- Reference products, variants, and other purchasable records only through the `sellable` polymorphic relationship. Never copy catalog ownership, pricing, stock, names, or product-specific classes into this module.
- Keep the package independent of product models, catalog implementations, and concrete tenant providers. Register `CartResource` through `CartPlugin`; sellables from any module resolve through the polymorphic relationship.
- Derive tenant awareness through `misaf/vendra-support`. Apply `BelongsToTenant` to `Cart`; items inherit tenant isolation through their parent cart. Never assign `tenant_id` directly or add a `tenant_aware` config toggle.
- Preserve the unique cart-item identity of `cart_id`, `sellable_type`, and `sellable_id`. Adding the same sellable should change quantity rather than create duplicate rows.
- Treat carts as temporary state. Keep expiration explicit and avoid turning carts into orders, payments, stock reservations, or immutable pricing snapshots; those concerns belong to checkout/order modules.
- Keep `CartResource` in the shared `SalesCluster` under `src/Filament/Clusters/Resources`, with forms in `Schemas`, tables in `Tables`, and items managed through the cart relation manager. The administration UI may inspect and delete carts/items but must not edit external sellables.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraCart\Filament\Clusters\Resources` namespace, and keep plugin registration aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `CartResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Carts`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Display resolved owner records through a human-readable label (`username`, `name`, or `email`) and eager-load the polymorphic owner instead of exposing raw morph type and ID columns.
- Use `CartPolicyEnum` and `CartItemPolicyEnum` as permission sources, keep policies aligned with exposed Filament actions, and update `PermissionPolicySeeder` when abilities change.
- Update English, German, and Persian translation files together and keep their keys in parity.
- Keep `DemoContentSeeder` standalone by resolving existing owners through Laravel's configured authentication model. Fall back to guest carts only when no owners exist, and never fabricate unresolved sellable references.
- Add focused Pest coverage for relationships, migration constraints, policies, Filament registration, configuration, and meaningful user-visible behavior.
- Keep architecture tests enforcing that `Misaf\VendraCart` does not use `Misaf\VendraTenant` or `Misaf\VendraProduct`.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.

- Adding an item locks the cart before its items, sharing the lock order used by checkout so additions cannot race with cart conversion.
