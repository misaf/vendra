# Vendra Address

Country-adaptable user profile addresses for Vendra applications.

## Features

- Dynamic `addresses` relation on Vendra user profiles
- Filament relation manager contributed through the User Profile extension registry
- International address shape: ISO country code, three address lines, locality, administrative area, and sorting code
- Structured JSON metadata for country-specific fields
- One default address per user profile, kept automatically
- Tenant-aware storage and permission-seeded authorization

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-user-profile`
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-address
php artisan vendor:publish --tag=vendra-address-migrations
php artisan migrate
```

Tenant columns are added automatically when a tenant provider is active. If
tenancy is enabled after this migration has run, use
`php artisan vendra-tenant:enable {tenant}` to retrofit the table and assign
existing unscoped records.

The service provider and the user-profile relation manager are auto-registered.

## Seeding

Address permissions seed automatically when a tenant is provisioned. To seed them manually:

```bash
php artisan vendra-address:seed {tenant}
```

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-address
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
