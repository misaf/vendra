# Vendra Support

Shared support infrastructure used by every Vendra module.

## Features

- Provider-neutral tenant resolution and tenant-awareness helpers
- Tenant-aware Eloquent traits, scopes, and migration helpers
- Optional tag, attribute, and currency integration contracts
- Provider-neutral subscription charging with idempotent operation results
- Plan entitlements: `PlanFeature` and `PlanLimit` enums, the `TenantEntitlements` contract, and the `TenantUsageRegistry` that domain packages report their usage to, the `TenantLimitOverages` lookup for limits a tenant already exceeds, and the `PlanUsageWidget` that shows a store its usage on the admin dashboard and flags any limit it is over
- Request and queued-job context propagation primitives
- Shared tenant seeders and console commands
- Shared Filament clusters, navigation taxonomy, and concerns
- `Settings\RegistersSettings`, which lets a package register its own settings classes and settings migrations from its service provider, and `Filament\Pages\SystemSettingsPage`, the base for a package's store settings page in the admin System cluster
- `IsActiveToggle` form field and `IsActiveToggleColumn` table column for boolean active/inactive columns
- `IsActiveEntry` infolist entry, `IsActiveConstraint` query-builder constraint, and `IsActiveFilter` ternary filter (Active / Inactive) for reading and filtering the same columns
- `IsDefaultToggle` form field, `IsDefaultEntry` infolist entry, `IsDefaultIconColumn` table column, `IsDefaultFilter` ternary filter, and `IsDefaultConstraint` query-builder constraint for boolean `is_default` columns
- `IsPrimaryToggle` form field and `IsPrimaryIconColumn` table column for boolean `is_primary` columns
- `Tenancy\TenantAwareness::constrainToCurrentTenant()` and `constrainToTenantOf()`, which limit a query to one tenant's rows, or to the tenantless rows outside a tenant, where the tenant scope applies nothing
- `Observers\Concerns\MaintainsSingleActiveDefault` observer trait that keeps exactly one active row flagged `is_default` per tenant (and one among tenantless rows), promotes the first active row (by `ordered`, else by key) when the default goes away, and clears the flag on a soft-deleted default
- `Observers\Concerns\MaintainsSingleFlagPerOwner` observer trait that keeps exactly one row per owner flagged (for example `is_default` or `is_primary`), gives the flag to an owner's first row, refuses to unflag the only one, and hands it to the oldest remaining row when it is deleted
- `CreatedAtColumn`, `UpdatedAtColumn`, `DeletedAtColumn`, and `RowIndexColumn` table columns for timestamps (Jalali under the `fa` locale) and the `#` row index
- `CreatedAtEntry`, `UpdatedAtEntry`, and `DateTimeEntry` infolist entries with the same timestamp formatting, `IsActiveIconColumn`, and `PositionConstraint`, `NameConstraint`, and `SlugConstraint` query-builder constraints
- `SluggableNameInput`, `SlugInput`, and `DescriptionTextarea` form fields, `NameEntry`, `SlugEntry`, and `DescriptionEntry` infolist entries, and `SlugColumn` for the common translated name/slug/description attributes
- `NameColumn` and `DescriptionColumn` table columns and the `DescriptionRichEditor` JSON rich-text field
- `Support\ContainsSearch` for case-insensitive "contains" searches across columns that treat `%` and `_` in the term literally
- Composable policy authorization, sandbox helpers, and shared events

The package binds null capability resolvers by default. Concrete providers can replace those bindings without coupling domain packages to their implementations.

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/filament-jalali`

## Installation

```bash
composer require misaf/vendra-support
```

The service provider is auto-registered.

Optionally publish the shared panel configuration:

```bash
php artisan vendor:publish --tag=vendra-support-config
```

A module registers its seed command class with `Tenancy\TenantSeeders`
(`register(SeedCommand::class, priority: 30)`). When a store is provisioned with
seeding, `Tenancy\Listeners\RunTenantSeeders` runs every registered command's
static `seeders()` directly inside the new tenant, not through Artisan, so seed
commands are registered with `hasConsoleCommand()` and provisioning still seeds
inside a web request.

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-support
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
