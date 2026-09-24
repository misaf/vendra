# Vendra Cart

Tenant-aware carts for Vendra applications, with guest UUID tokens, optional
polymorphic owners, polymorphic sellables, quantities, metadata, expiration,
Filament administration, permissions, and demo-content seeding.

## Features

- Guest carts identified by UUID tokens
- Optional polymorphic owners and polymorphic sellable items
- Quantity, metadata, and expiration tracking
- Tenant-aware Filament administration and permission seeding
- Scheduled pruning of expired carts

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-cart
php artisan vendor:publish --tag=vendra-cart-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-cart-config
php artisan vendor:publish --tag=vendra-cart-translations
```

The Filament resource is registered in the shared `Sales` cluster on the
configured panels. Cart creation and mutation remain application concerns; the
administration UI is limited to viewing and deletion.

Expired carts are pruned daily by default. Change the schedule in the
published configuration or run `php artisan vendra-cart:prune-expired`
manually. `Cart::query()->expired()` and `unexpired()` select the same carts; a
cart without `expires_at` never expires.

Demo seeders use bundled JSON fixtures in production and when their declared factory classes are unavailable. Local monorepo development continues to use factories when they are autoloadable.

Adding an item locks the cart before its items, sharing the lock order used by checkout so additions cannot race with cart conversion.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-cart
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
