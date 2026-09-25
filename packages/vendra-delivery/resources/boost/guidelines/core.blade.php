## Vendra Delivery

The `misaf/vendra-delivery` package owns delivery zones, delivery windows, bookable dates, fee rules, the delivery scheduled against a placed order, and the Filament administration UI for all of them.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the calendar rules `advance_days` and `same_day_cutoff_hour`) live in `Settings\DeliverySettings` (group `delivery`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageDeliverySettings` edits them in the admin System cluster; read them with `resolve(DeliverySettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

- Keep delivery domain code inside `packages/vendra-delivery` using the `Misaf\VendraDelivery` namespace.
- `DeliveryZone` is a distance band anchored to its own origin, `DeliverySlot` is a window of the day, and `Delivery` is where and when one order travels plus the fee snapshot it was charged.
- Point the dependency arrow delivery → order, never back. `misaf/vendra-order` must not know this module exists; a delivery references its order, and the order keeps its own `delivery_amount` snapshot.
- Reuse `misaf/vendra-address` for addresses. Never model a second address, and never copy address fields onto a delivery beyond the recipient name.
- Keep zone matching a read: `DeliveryZoneMatcher` decides nothing and records nothing, so it stays a plain service. Only `ScheduleDeliveryAction` writes.
- Consult bands in `position` order — tightest radius first — and let the first band that covers the point price the delivery. A band without `max_distance_km` is the outermost catch-all.
- Refuse to schedule a quote whose `requires_quote` is true. An address the studio prices by hand must reach a human, never a guessed fee.
- Treat the fee on a delivery as a snapshot: re-pricing a zone later never rewrites what a customer was charged.
- Derive tenant awareness through `misaf/vendra-support`. Apply `BelongsToTenant` to all three models and register their tables with `TenantTableRegistry`.
- Keep zones, windows, and fees as tenant business data in the administration UI, and the calendar rules in `DeliverySettings`.
- Keep `DeliveryZoneResource`, `DeliverySlotResource`, and `DeliveryResource` in the shared `SalesCluster` under `src/Filament/Clusters/Resources`, with forms in `Schemas` and tables in `Tables`.
- Keep the complete resource tree under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraDelivery\Filament\Clusters\Resources` namespace, and keep plugin registration aligned. Any future resource without a `$cluster` must instead live under `src/Filament/Resources/`.
- Keep the resources ungrouped and assign `$navigationSort` from `NavigationPriority::DeliveryZones`, `NavigationPriority::DeliverySlots`, and `NavigationPriority::Deliveries`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep deliveries uncreatable by hand in the administration UI; checkout schedules them.
- Use `DeliveryZonePolicyEnum`, `DeliverySlotPolicyEnum`, and `DeliveryPolicyEnum` as permission sources, and update `PermissionPolicySeeder` when abilities change.
- Update English, German, and Persian translation files together and keep their keys in parity.
- Add focused Pest coverage for band matching, the same-day cutoff, scheduling, migration constraints, policies, and Filament registration.
- Keep architecture expectations enforcing that `Misaf\VendraDelivery` does not use `Misaf\VendraTenant` or `Misaf\VendraProduct`.
