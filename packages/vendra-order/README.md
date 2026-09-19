# Vendra Order

Tenant-aware orders for Vendra applications: immutable order lines with money
snapshots, cart-to-order conversion, an explicit order lifecycle, bank-transfer
references against a `misaf/vendra-transaction` gateway, Filament
administration, and permission seeding.

## Features

- Orders with a human-readable number, optional polymorphic customer, and money snapshots in minor units
- Immutable order lines that snapshot the purchased sellable's translatable name, unit amount, and quantity
- Cart-to-order conversion through `PlaceOrderAction`
- Explicit lifecycle: pending → confirmed → completed, with cancellation from either open state
- Optional payment gateway reference reusing `misaf/vendra-transaction` gateways
- Tenant-aware Filament administration and permission seeding

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`, `misaf/vendra-cart`, `misaf/vendra-transaction`

## Installation

```bash
composer require misaf/vendra-order
php artisan vendor:publish --tag=vendra-order-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-order-config
php artisan vendor:publish --tag=vendra-order-translations
```

The Filament resource is registered in the shared `Sales` cluster on the
configured panels. Orders are created by the application through
`PlaceOrderAction`; the administration UI inspects orders and moves them
through their lifecycle.

## Placing an order

The module never reads the catalog. Callers price the lines and hand the action
ready-made drafts, so the order stores a snapshot that survives later catalog
changes:

```php
use Misaf\VendraOrder\Actions\PlaceOrderAction;
use Misaf\VendraOrder\Data\OrderLineDraft;

$order = app(PlaceOrderAction::class)->execute(
    cart: $cart,
    currencyCode: 'USD',
    lines: [
        new OrderLineDraft(
            sellable: $product,
            name: ['en' => 'Marigold Morning'],
            unitAmount: 4800,
            quantity: 2,
        ),
    ],
    customer: $user,
    deliveryAmount: 0,
    cardMessage: 'Happy birthday.',
);
```

Cart conversion locks and rechecks the persisted cart before creating an order. Empty or already consumed carts are rejected, including stale model instances. The cart token can be reused after adding new items.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-order
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
