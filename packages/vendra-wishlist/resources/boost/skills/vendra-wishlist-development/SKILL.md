---
name: vendra-wishlist-development
description: "Create, modify, review, or test the Vendra Wishlist module in packages/vendra-wishlist. Use for Wishlist, WishlistItem, favourites and saved items, default lists, AddWishlistItemAction, RemoveWishlistItemAction, wishlist migrations and factories, wishlist policies and permission seeders, the vendra-wishlist:seed command, WishlistPlugin, CustomersCluster, WishlistResource, wishlist item relation managers, translations, or configuration."
---

# Vendra Wishlist

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-wishlist` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the name of the default list) live in `Settings\WishlistSettings` (group `wishlist`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageWishlistSettings` edits them in the admin System cluster; read them with `resolve(WishlistSettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

Use this skill with `laravel-best-practices` for Laravel PHP, `pest-testing` when tests change, and `vendra-permission-development` when policies or permissions change. Use `tailwindcss-development` only for Blade or Tailwind UI.

## Module Boundary

Treat `packages/vendra-wishlist` as the source of saved-selection behavior and its Filament administration UI.

- Use namespace `Misaf\VendraWishlist`.
- Keep models, factories, migrations, policies, permission enums, seed commands, Filament classes, config, translations, and tests inside this module.
- Keep carts, orders, checkout, pricing, stock, and catalog ownership outside this module.
- Keep API schemas and routes in `misaf/vendra-wishlist-api`.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Model a wishlist with an opaque UUID token, optional polymorphic `owner`, a scalar `name`, an `is_default` flag, and an `items()` relationship.
- Model each item with a parent list, polymorphic `sellable`, and optional JSON metadata for selection-specific values.
- Keep one default list per owner and resolve it with `Wishlist::firstOrCreateDefaultFor()`; a heart button on a product card has no list to pick. The `wishlists_default_owner_unique` index on the virtual `default_list_guard` column enforces one default list, so concurrent first saves resolve to the same list.
- Keep saving idempotent and locked; keep removing forgiving.
- Reference catalog records; never duplicate or own product names, descriptions, prices, stock, or lifecycle data.
- Use typed Eloquent relationships with PHPDoc generics, Laravel model attributes, explicit casts, final classes, and `declare(strict_types=1)`.

## Tenant Awareness

- Derive tenancy only from `misaf/vendra-support`; never reference `Misaf\VendraTenant`.
- Apply `BelongsToTenant` to `Wishlist` and use `TenantSchema` in its migration.
- Do not apply a second tenant scope to `WishlistItem`; tenant isolation flows through the required wishlist relationship.
- Never assign `tenant_id` manually or add a `tenant_aware` configuration value.
- Keep factories and permission seeders functional with tenancy enabled or disabled.

## Filament And Permissions

- Register `WishlistResource` through `WishlistPlugin` and `WishlistServiceProvider`, respecting configured panel IDs.
- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraWishlist\Filament\Clusters\Resources` namespace and plugin registration. Resources without a cluster belong under `src/Filament/Resources/`; delegate schemas and tables to dedicated classes.
- Keep the administration surface read-and-delete only: a list belongs to its customer, so never create or edit one on their behalf.
- Display the resolved owner using `username`, `name`, or `email` with a route-key fallback, and eager-load the polymorphic owner.
- Use Filament v5 namespaces: fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, columns from `Filament\Tables\Columns`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use `vendra-wishlist::attributes` and `vendra-wishlist::navigation` translation keys for every visible label.
- Keep `WishlistResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Wishlists`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep policy methods aligned with exposed actions and backed by `WishlistPolicyEnum` or `WishlistItemPolicyEnum` permissions.
- Update `PermissionPolicySeeder` and `vendra-wishlist:seed` whenever permission cases change.

## Data And Localization

- Keep the package migration reversible and ensure deleting a list cascades to its items without affecting sellables.
- Keep list tokens unique and preserve the unique key on `wishlist_id`, `sellable_type`, and `sellable_id`.
- Update English, German, and Persian locale files together, with sorted keys and parity.
- Keep metadata optional and selection-focused; never use it as an unstructured copy of catalog data.

## Testing And Verification

- Add focused Pest tests for idempotent saving, removal, default-list resolution, polymorphic relationships, migration constraints, policy permissions, plugin/resource registration, and tenant independence.
- Add Livewire tests when Filament interaction behavior changes materially.
- Run `php artisan test --compact packages/vendra-wishlist/tests`.
- Run PHPStan against the module source, factories, and seeders.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.
