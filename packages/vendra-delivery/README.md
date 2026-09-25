# Vendra Delivery

Tenant-aware delivery for Vendra applications: distance bands that price a
dropped pin, the windows of the day a customer can choose, bookable dates, and
the scheduled delivery attached to a placed order.

## Features

- `DeliveryZone` bands measured from the studio, ordered tightest-first, each with its own fee
- An outermost band that is quoted by hand and refuses checkout instead of inventing a price
- `DeliverySlot` windows with optional per-date capacity
- Bookable dates that respect each store's same-day cutoff
- `Delivery` records the address, pin, distance, window, date and the fee snapshot for one order
- Tenant-aware Filament administration and permission seeding

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `misaf/vendra-support`, `misaf/vendra-order`, `misaf/vendra-address`

## Installation

```bash
composer require misaf/vendra-delivery
php artisan vendor:publish --tag=vendra-delivery-migrations
php artisan migrate
```

Optionally publish the configuration and translations:

```bash
php artisan vendor:publish --tag=vendra-delivery-config
php artisan vendor:publish --tag=vendra-delivery-translations
```

## Pricing a pin

```php
use Misaf\VendraDelivery\Actions\ScheduleDeliveryAction;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;

$quote = app(DeliveryZoneMatcher::class)->quoteFor(35.7219, 51.3347, 'USD');

if (! $quote->isDeliverable()) {
    // Outside the delivered range — the studio quotes this address by hand.
}

app(ScheduleDeliveryAction::class)->execute(
    order: $order,
    quote: $quote,
    scheduledFor: '2026-09-01',
    slot: $slot,
    recipientName: 'Nasrin K.',
    latitude: 35.7219,
    longitude: 51.3347,
);
```

Zones, windows and fees are tenant business data managed in the administration
UI. The calendar rules, how far ahead customers may book and the same-day
cutoff hour, are store settings (`Settings\DeliverySettings`) edited on the
delivery settings page.

## Testing

```bash
php artisan test --compact --testsuite=vendra-delivery
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
