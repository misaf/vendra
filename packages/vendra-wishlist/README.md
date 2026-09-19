# Vendra Wishlist

Tenant-aware saved selections for Vendra applications: customers keep one or
more lists of things they want to come back to, with a default list a heart
button on a product card writes to.

## Features

- Guest lists identified by UUID tokens, plus optional polymorphic owners
- One default list per owner, created on first use through `Wishlist::firstOrCreateDefaultFor()` and enforced by a unique index
- Polymorphic saved items with optional selection metadata
- Idempotent saving: tapping the heart twice keeps one row, not two
- Tenant-aware Filament administration and permission seeding

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`

## Installation

```bash
composer require misaf/vendra-wishlist
php artisan vendor:publish --tag=vendra-wishlist-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-wishlist-config
php artisan vendor:publish --tag=vendra-wishlist-translations
```

## Saving something

```php
use Misaf\VendraWishlist\Actions\AddWishlistItemAction;
use Misaf\VendraWishlist\Actions\RemoveWishlistItemAction;
use Misaf\VendraWishlist\Models\Wishlist;

$wishlist = Wishlist::firstOrCreateDefaultFor($user);

app(AddWishlistItemAction::class)->execute($wishlist, $product);

$wishlist->has($product);  // true

app(RemoveWishlistItemAction::class)->execute($wishlist, $product);
```

A wishlist is not a cart: nothing here is reserved, priced, or expiring. Carts
belong to `misaf/vendra-cart` and orders to `misaf/vendra-order`.

## Testing

```bash
php artisan test --compact --testsuite=vendra-wishlist
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
