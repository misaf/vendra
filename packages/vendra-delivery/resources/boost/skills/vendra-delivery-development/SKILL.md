---
name: vendra-delivery-development
description: "Create, modify, review, or test the Vendra Delivery module in packages/vendra-delivery. Use for DeliveryZone, DeliverySlot, Delivery, delivery bands and radii, delivery fees, dropped pins and distances, DeliveryZoneMatcher, DeliveryQuote, DeliverySchedule, bookable dates and same-day cutoffs, ScheduleDeliveryAction, delivery migrations and factories, delivery policies and permission seeders, the vendra-delivery:seed command, DeliveryPlugin, SalesCluster delivery resources, translations, or configuration."
---

# Vendra Delivery

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-delivery` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the calendar rules `advance_days` and `same_day_cutoff_hour`) live in `Settings\DeliverySettings` (group `delivery`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageDeliverySettings` edits them in the admin System cluster; read them with `resolve(DeliverySettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

Use this skill with `laravel-best-practices` for Laravel PHP, `pest-testing` when tests change, and `vendra-permission-development` when policies or permissions change. Use `tailwindcss-development` only for Blade or Tailwind UI.

## Module Boundary

Treat `packages/vendra-delivery` as the source of delivery zoning, scheduling, and fee behavior plus its Filament administration UI.

- Use namespace `Misaf\VendraDelivery`.
- Keep models, factories, migrations, policies, permission enums, seed commands, Filament classes, config, translations, and tests inside this module.
- Keep orders, order lines, and order lifecycle in `misaf/vendra-order`; keep addresses in `misaf/vendra-address`; keep catalog and payment concerns out entirely.
- Keep API schemas and routes in `misaf/vendra-delivery-api`.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Anchor each zone to its own `origin_latitude`/`origin_longitude` so a tenant with two studios can price from either.
- Measure distance with `GeoDistance::kilometres()`; bands are kilometres wide, so a spherical haversine is precise enough and needs no projection.
- Order bands by `position`, tightest radius first, and stop at the first band that covers the point.
- Model the "beyond our usual range" band as an active zone with a null `max_distance_km` and `requires_quote` set.
- Return a `DeliveryQuote` from matching; it carries the zone, distance, fee in minor units, currency, and whether the address must be quoted by hand.
- Store money as unsigned integers in minor units cast with `MoneyIntegerCast` against the row's own `currency_code`.
- Keep one delivery per order: `ScheduleDeliveryAction` locks and updates the existing row rather than inserting a second.
- Treat bookable dates as a business rule in `DeliverySchedule`, not a client concern: an order placed after the same-day cutoff starts from tomorrow.
- Use typed Eloquent relationships with PHPDoc generics, Laravel model attributes, explicit casts, final classes, and `declare(strict_types=1)`.

## Tenant Awareness

- Derive tenancy only from `misaf/vendra-support`; never reference `Misaf\VendraTenant`.
- Apply `BelongsToTenant` to `DeliveryZone`, `DeliverySlot`, and `Delivery`, and use `TenantSchema` in the migration.
- Never assign `tenant_id` manually or add a `tenant_aware` configuration value.
- Keep factories and permission seeders functional with tenancy enabled or disabled.

## Filament And Permissions

- Register the resources through `DeliveryPlugin` and `DeliveryServiceProvider`, respecting configured panel IDs.
- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraDelivery\Filament\Clusters\Resources` namespace and plugin registration. Resources without a cluster belong under `src/Filament/Resources/`; delegate schemas and tables to dedicated classes.
- Keep zones and windows fully editable and reorderable; keep deliveries view-only apart from deletion.
- Use Filament v5 namespaces: fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, columns from `Filament\Tables\Columns`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use `vendra-delivery::attributes` and `vendra-delivery::navigation` translation keys for every visible label.
- Keep the resources ungrouped and assign `$navigationSort` from `NavigationPriority::DeliveryZones`, `NavigationPriority::DeliverySlots`, and `NavigationPriority::Deliveries`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep policy methods aligned with exposed actions and backed by the module's permission enums.
- Update `PermissionPolicySeeder` and `vendra-delivery:seed` whenever permission cases change.

## Data And Localization

- Keep the package migration reversible and ensure deleting an order cascades to its delivery while zones and windows are restricted from deletion while referenced.
- Keep one delivery per order enforced by a unique index inside the tenant boundary.
- Update English, German, and Persian locale files together, with sorted keys and parity.

## Testing And Verification

- Add focused Pest tests for band selection, out-of-range quotes, the same-day cutoff, rescheduling, capacity, migration constraints, policy permissions, and plugin/resource registration.
- Add Livewire tests when Filament interaction behavior changes materially.
- Run `php artisan test --compact packages/vendra-delivery/tests`.
- Run PHPStan against the module source, factories, and seeders.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.
