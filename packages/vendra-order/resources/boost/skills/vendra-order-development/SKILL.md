---
name: vendra-order-development
description: "Create, modify, review, or test the Vendra Order module in packages/vendra-order. Use for Order, OrderLine, checkout, cart-to-order conversion, PlaceOrderAction, CancelOrderAction, CancelOrderTransition, OrderCancelled, stock_deducted, OrderLineDraft, order numbers, order lifecycle states (pending, confirmed, completed, cancelled), money snapshots, payment references against transaction gateways, order migrations and factories, order policies and permission seeders, the vendra-order:seed command, OrderPlugin, SalesCluster, OrderResource, order line relation managers, translations, or configuration."
---

# Vendra Order

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-order` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the order number prefix) live in `Settings\OrderSettings` (group `order`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageOrderSettings` edits them in the admin System cluster; read them with `resolve(OrderSettings::class)`, never from config.

Use this skill with `laravel-best-practices` for Laravel PHP, `pest-testing` when tests change, and `vendra-permission-development` when policies or permissions change. Use `tailwindcss-development` only for Blade or Tailwind UI.

## Module Boundary

Treat `packages/vendra-order` as the source of placed-order behavior and its Filament administration UI.

- Use namespace `Misaf\VendraOrder`.
- Keep models, factories, migrations, policies, permission enums, seed commands, Filament classes, config, translations, and tests inside this module.
- Keep catalog ownership, pricing rules, cart mutation, payment execution, wallets, ledgers, and delivery scheduling outside this module.
- Keep API schemas and routes in `misaf/vendra-order-api`.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Model an order with a generated `number`, optional polymorphic `customer`, optional source `cart`, optional `transactionGateway`, a `status` state cast, `currency_code`, money snapshots (`items_amount`, `delivery_amount`, `total_amount`), an optional `payment_reference`, an optional `card_message`, and `lines()`.
- Model each line with its parent order, polymorphic `sellable`, translatable `name` snapshot, currency code, positive quantity, unit amount, line amount, and optional JSON metadata.
- Store money as unsigned integers in minor units cast with `MoneyIntegerCast` against the row's own `currency_code`.
- Resolve prices outside this module. `PlaceOrderAction::execute()` accepts `OrderLineDraft` values and performs the whole conversion — order, lines, and cart clearing — inside one `DB::transaction()`.
- Every cancellation runs `States\CancelOrderTransition`, which locks the order and dispatches `Events\OrderCancelled` inside its transaction so stock listeners commit or roll back with it. Never set the status to `Cancelled` by hand. `stock_deducted` records that the caller took stock at placement (`PlaceOrderAction`'s `stockDeducted`).
- Leave the cart row intact after conversion so its token stays usable; only its items are cleared.
- Never mutate a placed order's lines or amounts. Corrections are new domain events, not edits.
- Use typed Eloquent relationships with PHPDoc generics, Laravel model attributes, explicit casts, final classes, and `declare(strict_types=1)`.

## Tenant Awareness

- Derive tenancy only from `misaf/vendra-support`; never reference `Misaf\VendraTenant`.
- Apply `BelongsToTenant` to `Order` and use `TenantSchema` in its migration.
- Do not apply a second tenant scope to `OrderLine`; tenant isolation flows through the required order relationship.
- Never assign `tenant_id` manually or add a `tenant_aware` configuration value.
- Keep factories and permission seeders functional with tenancy enabled or disabled.

## Filament And Permissions

- Register `OrderResource` through `OrderPlugin` and `OrderServiceProvider`, respecting configured panel IDs.
- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraOrder\Filament\Clusters\Resources` namespace and plugin registration. Resources without a cluster belong under `src/Filament/Resources/`; delegate schemas and tables to dedicated classes.
- Expose lifecycle changes as dedicated Filament actions guarded by `canTransitionTo()`; never write the status column directly.
- Keep the order lines relation manager read-only.
- Use Filament v5 namespaces: fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, columns from `Filament\Tables\Columns`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use `vendra-order::attributes`, `vendra-order::navigation`, `vendra-order::enums`, and `vendra-order::messages` translation keys for every visible label.
- Keep `OrderResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Orders`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep policy methods aligned with exposed actions and backed by `OrderPolicyEnum` or `OrderLinePolicyEnum` permissions.
- Update `PermissionPolicySeeder` and `vendra-order:seed` whenever permission cases change.

## Data And Localization

- Keep the package migration reversible and ensure deleting an order cascades to its lines without touching carts, gateways, or sellables.
- Keep order numbers unique within the tenant boundary and index status, currency, and placement time.
- Update English, German, and Persian locale files together, with sorted keys and parity.
- Keep line metadata optional and purchase-specific; never use it as an unstructured copy of catalog data.

## Testing And Verification

- Add focused Pest tests for placement, lifecycle transitions, immutability, migration constraints, policy permissions, plugin/resource registration, and tenant independence.
- Add Livewire tests when Filament interaction behavior changes materially.
- Run `php artisan test --compact packages/vendra-order/tests`.
- Run PHPStan against the module source, factories, and seeders.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.

- Cart conversion locks and rechecks the persisted cart before creating an order. Empty or already consumed carts are rejected, including stale model instances. The cart token can be reused after adding new items.
