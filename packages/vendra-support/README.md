# Vendra Support

Shared support infrastructure used by every Vendra module.

## Features

- Provider-neutral tenant resolution and tenant-awareness helpers
- Tenant-aware Eloquent traits, scopes, and migration helpers
- Optional tag, attribute, and currency integration contracts
- Provider-neutral subscription charging with idempotent operation results
- Request and queued-job context propagation primitives
- Shared tenant seeders and console commands
- Shared Filament clusters, navigation taxonomy, and concerns
- `IsActiveToggle` form field and `IsActiveToggleColumn` table column for boolean active/inactive columns
- `IsDefaultToggle` form field, `IsDefaultEntry` infolist entry, and `IsDefaultConstraint` query-builder constraint for boolean `is_default` columns
- `CreatedAtColumn`, `UpdatedAtColumn`, and `RowIndexColumn` table columns for timestamps (Jalali under the `fa` locale) and the `#` row index
- `SluggableNameInput`, `SlugInput`, and `DescriptionTextarea` form fields, `NameEntry`, `SlugEntry`, and `DescriptionEntry` infolist entries, and `SlugColumn` for the common translated name/slug/description attributes
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

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-support
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
