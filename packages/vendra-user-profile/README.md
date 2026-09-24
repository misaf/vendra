# Vendra User Profile

Tenant-aware user profiles and Filament profile management for Vendra applications.

## Features

- Multiple named profiles per user
- One default profile per user, kept automatically
- Tenant-aware profile storage and authorization
- Filament resource with create, view, edit, and list pages
- Pennant-controlled module availability

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- Laravel Pennant 1
- `misaf/vendra-user`
- `misaf/vendra-permission`
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-user-profile
php artisan vendor:publish --tag=vendra-user-profile-migrations
php artisan migrate
```

Optionally publish configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-user-profile-config
php artisan vendor:publish --tag=vendra-user-profile-translations
```

Tenant columns are added automatically when a tenant provider is active. If
tenancy is enabled after this migration has run, use
`php artisan vendra-tenant:enable {tenant}` to retrofit the table and assign
existing unscoped records.

The service provider and Filament plugin are auto-registered.

## Feature Flag

The resource is enabled by default through a class-based, tenant-scoped Pennant
feature. Disable feature resolution or the module globally in the published
configuration when needed:

```php
'features_enabled' => false,
'module_enabled' => false,
```

Set `features_discover` to `true` when the host needs this package's feature
class included in `Feature::all()` before it has been checked.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-user-profile
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
