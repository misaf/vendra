## Vendra Wishlist

The `misaf/vendra-wishlist` package owns saved product selections — wishlists and their items — and the Filament administration UI for inspecting them.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the name of the default list) live in `Settings\WishlistSettings` (group `wishlist`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageWishlistSettings` edits them in the admin System cluster; read them with `resolve(WishlistSettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

- Keep wishlist domain code inside `packages/vendra-wishlist` using the `Misaf\VendraWishlist` namespace.
- `Wishlist` owns its opaque token, optional polymorphic owner, name, default flag, and items. `WishlistItem` stores the saved sellable and optional selection metadata.
- A customer-entered list name is user data, not a translated string: keep `name` a scalar column and out of `$translatable`.
- Reference products, variants, and other saved records only through the `sellable` polymorphic relationship. Never copy catalog ownership, pricing, stock, names, or product-specific classes into this module.
- Keep the package free of cart, order, checkout, pricing, and stock concerns. A wishlist reserves nothing and prices nothing; carts belong to `misaf/vendra-cart` and orders to `misaf/vendra-order`.
- Resolve the list a heart button writes to through `Wishlist::firstOrCreateDefaultFor()` so callers never have to invent one, and keep exactly one default list per owner. The `wishlists_default_owner_unique` index on the virtual `default_list_guard` column enforces that, so concurrent first saves resolve to the same list.
- Keep saving idempotent: `AddWishlistItemAction` takes a row lock so two taps racing each other cannot both insert past the unique index, and removing something that was never saved is not an error.
- Preserve the unique identity of `wishlist_id`, `sellable_type`, and `sellable_id`.
- Derive tenant awareness through `misaf/vendra-support`. Apply `BelongsToTenant` to `Wishlist`; items inherit tenant isolation through their parent list. Never assign `tenant_id` directly or add a `tenant_aware` config toggle.
- Keep `WishlistResource` in the shared `CustomersCluster` under `src/Filament/Clusters/Resources`, with forms in `Schemas`, tables in `Tables`, and items managed through the relation manager.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraWishlist\Filament\Clusters\Resources` namespace, and keep plugin registration aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `WishlistResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Wishlists`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep administration read-and-delete only: never create or edit a customer's list on their behalf.
- Display resolved owner records through a human-readable label (`username`, `name`, or `email`) and eager-load the polymorphic owner instead of exposing raw morph type and ID columns.
- Use `WishlistPolicyEnum` and `WishlistItemPolicyEnum` as permission sources, and update `PermissionPolicySeeder` when abilities change.
- Update English, German, and Persian translation files together and keep their keys in parity.
- Add focused Pest coverage for idempotent saving, default-list resolution, migration constraints, policies, and Filament registration.
- Keep architecture expectations enforcing that `Misaf\VendraWishlist` does not use `Misaf\VendraTenant` or `Misaf\VendraProduct`.
