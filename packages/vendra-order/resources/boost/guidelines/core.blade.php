## Vendra Order

The `misaf/vendra-order` package owns placed orders, their immutable line snapshots, cart-to-order conversion, the order lifecycle, and the Filament administration UI for inspecting and progressing orders.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the order number prefix) live in `Settings\OrderSettings` (group `order`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageOrderSettings` edits them in the admin System cluster; read them with `resolve(OrderSettings::class)`, never from config.

- Keep order domain code inside `packages/vendra-order` using the `Misaf\VendraOrder` namespace.
- `Order` owns the customer-facing number, optional polymorphic customer, lifecycle state, money snapshots in minor units, the optional payment gateway reference, and its lines. `OrderLine` is an immutable purchase snapshot.
- Never read the catalog from this module. Callers price the purchase and hand `PlaceOrderAction` a list of `OrderLineDraft` values; the action snapshots them. Products, variants, and other purchasable records are referenced only through the `sellable` polymorphic relationship.
- Reuse `misaf/vendra-cart` for pre-checkout state and `misaf/vendra-transaction` for payment gateways. Never re-implement carts, gateways, wallets, or ledgers here, and never add a separate checkout, payment, or bank-transfer package.
- Keep delivery scheduling, zones, and fee rules in `misaf/vendra-delivery`. This module only stores the resulting `delivery_amount` snapshot, so the arrow points delivery → order and never back.
- Derive tenant awareness through `misaf/vendra-support`. Apply `BelongsToTenant` to `Order`; lines inherit tenant isolation through their parent order. Never assign `tenant_id` directly or add a `tenant_aware` config toggle.
- Keep the lifecycle in `src/States` with `spatie/laravel-model-states`: pending → confirmed → completed, cancellable from either open state. Never add a status string column outside that state cast, and never widen the transition table without a domain reason.
- Every cancellation, including `Order::cancel()`, runs `States\CancelOrderTransition`, which locks the order and dispatches `Events\OrderCancelled` inside its transaction (not after commit) so stock listeners share the outcome. Never assign `Cancelled` to the status directly. `stock_deducted`, set through `PlaceOrderAction`'s `stockDeducted`, tells listeners the order holds stock.
- Treat placed orders as financial records: lines, amounts, and currency codes are written once by `PlaceOrderAction` and never edited afterwards. `OrderLinePolicy` denies create, update, and delete.
- Keep `OrderResource` in the shared `SalesCluster` under `src/Filament/Clusters/Resources`, with forms in `Schemas`, tables in `Tables`, lifecycle buttons in `Actions`, and lines exposed through the read-only relation manager.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraOrder\Filament\Clusters\Resources` namespace, and keep plugin registration aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep `OrderResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Orders`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Display resolved customer records through a human-readable label (`username`, `name`, or `email`) and eager-load the polymorphic customer instead of exposing raw morph type and ID columns.
- Use `OrderPolicyEnum` and `OrderLinePolicyEnum` as permission sources, keep policies aligned with exposed Filament actions, and update `PermissionPolicySeeder` when abilities change.
- Update English, German, and Persian translation files together and keep their keys in parity.
- Add focused Pest coverage for the placement action, lifecycle transitions, migration constraints, policies, Filament registration, and configuration.
- Keep architecture expectations enforcing that `Misaf\VendraOrder` does not use `Misaf\VendraTenant` or `Misaf\VendraProduct`.

- Cart conversion locks and rechecks the persisted cart before creating an order. Empty or already consumed carts are rejected, including stale model instances. The cart token can be reused after adding new items.
