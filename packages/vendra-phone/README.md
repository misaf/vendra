# Vendra Phone

International user profile phone numbers for Vendra applications.

## Features

- Dynamic `phoneNumbers` relation on Vendra user profiles
- Filament relation manager contributed through the User Profile extension registry
- Canonical E.164 number storage with a separate ISO country state via `ysfkaya/filament-phone-input`
- Open phone type strings and structured JSON metadata for country- or carrier-specific fields
- One primary number per user profile, kept automatically
- Tenant-aware storage and permission-seeded authorization

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-user-profile`
- `misaf/vendra-support`
- `ysfkaya/filament-phone-input`

## Installation

```bash
composer require misaf/vendra-phone
php artisan vendor:publish --tag=vendra-phone-migrations
php artisan migrate
```

Tenant columns are added automatically when a tenant provider is active. If
tenancy is enabled after this migration has run, use
`php artisan vendra-tenant:enable {tenant}` to retrofit the table and assign
existing unscoped records.

The service provider and the user-profile relation manager are auto-registered.

## Seeding

Phone number permissions seed automatically when a tenant is provisioned. To seed them manually:

```bash
php artisan vendra-phone:seed {tenant}
```

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-phone
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
