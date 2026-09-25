---
name: vendra-cart-development
description: "Create, modify, review, or test the Vendra Cart module in packages/vendra-cart. Use for Cart, CartItem, temporary shopping selections, polymorphic owners or sellables, cart expiration, cart migrations and factories, cart policies and permission seeders, the vendra-cart:seed command, CartPlugin, SalesCluster, CartResource, cart item relation managers, translations, configuration, or checkout-boundary decisions involving carts."
---

# Vendra Cart

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-cart` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (how many days a new cart lives, or null for no expiry) live in `Settings\CartSettings` (group `cart`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageCartSettings` edits them in the admin System cluster; read them with `resolve(CartSettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

Use this skill with `laravel-best-practices` for Laravel PHP, `pest-testing` when tests change, and `vendra-permission-development` when policies or permissions change. Use `tailwindcss-development` only for Blade or Tailwind UI.

## Module Boundary

Treat `packages/vendra-cart` as the source of temporary shopping-selection behavior and its Filament administration UI.

- Use namespace `Misaf\VendraCart`.
- Keep models, factories, migrations, policies, permission enums, seed commands, Filament classes, config, translations, and tests inside this module.
- Keep checkout, orders, payment, inventory reservation, and catalog ownership outside this module.
- Keep API schemas and routes in a dedicated API module if introduced later.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Model a cart with an opaque UUID token, optional polymorphic `owner`, optional `expires_at`, and `items()` relationship.
- Model each item with a parent cart, polymorphic `sellable`, positive quantity, and optional JSON metadata for selection-specific values.
- Reference catalog records; never duplicate or own product/variant names, descriptions, prices, stock, or lifecycle data.
- Keep the package independent of concrete catalog implementations. Register its administration resource standalone; products, variants, and other sellables resolve through `MorphTo`.
- Preserve the unique database key on `cart_id`, `sellable_type`, and `sellable_id`. Merge quantity for an existing sellable instead of creating a duplicate item.
- Store pricing snapshots only when a checkout/order requirement explicitly introduces that boundary. Do not silently turn temporary cart rows into financial records.
- Use typed Eloquent relationships with PHPDoc generics, Laravel model attributes, explicit casts, final classes, and `declare(strict_types=1)`.

## Tenant Awareness

- Derive tenancy only from `misaf/vendra-support`; never reference `Misaf\VendraTenant`.
- Apply `BelongsToTenant` to `Cart` and use `TenantSchema` in its migration.
- Do not apply a second tenant scope to `CartItem`; tenant isolation flows through the required cart relationship.
- Never assign `tenant_id` manually or add a `tenant_aware` configuration value.
- Keep factories, permission seeders, and `DemoContentSeeder` functional with tenancy enabled or disabled.

## Filament And Permissions

- Register `CartResource` through `CartPlugin` and `CartServiceProvider`, respecting configured panel IDs.
- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraCart\Filament\Clusters\Resources` namespace and plugin registration. Resources without a cluster belong under `src/Filament/Resources/`; delegate schemas and tables to dedicated classes.
- Keep the administration surface operational and catalog-safe: allow authorized users to list, view, and delete carts or items, but do not edit external sellable data.
- Display the resolved owner using `username`, `name`, or `email` with a route-key fallback. Eager-load the polymorphic owner in tables and do not expose raw morph type/ID as the primary label.
- Use Filament v5 namespaces: fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, columns from `Filament\Tables\Columns`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use `vendra-cart::attributes` and `vendra-cart::navigation` translation keys for every visible label.
- Keep `CartResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Carts`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep policy methods aligned with exposed actions and backed by `CartPolicyEnum` or `CartItemPolicyEnum` permissions.
- Update `PermissionPolicySeeder` and `vendra-cart:seed` whenever permission cases change.

## Data And Localization

- Keep the package migration reversible and ensure deleting a cart cascades to its items without affecting sellables.
- Keep UUID cart tokens unique and expiration indexed within the tenant boundary.
- Update English, German, and Persian locale files together, with sorted keys and parity.
- Keep metadata optional and selection-focused; avoid using it as an unstructured copy of catalog data.
- Resolve demo cart owners through Laravel's configured authentication model and rely on its tenant scope. Fall back to guest carts only when no owners exist; do not fabricate polymorphic sellables that may not exist in the host application.

## Testing And Verification

- Add focused Pest tests for polymorphic relationships, defaults, migration constraints, policy permissions, plugin/resource registration, and tenant independence.
- Add Livewire tests when Filament interaction behavior changes materially.
- Keep `tests/ArchTest.php` enforcing no dependency on `Misaf\VendraTenant` or `Misaf\VendraProduct`.
- Run `php artisan test --compact packages/vendra-cart/tests`.
- Run PHPStan against the module source, factories, and seeders.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.

- Adding an item locks the cart before its items, sharing the lock order used by checkout so additions cannot race with cart conversion.
