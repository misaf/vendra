# Vendra Order API

API Platform resources for the Vendra Order module: customers read their own
orders and convert their cart into one through a single checkout operation.

## Endpoints

| Method | URI | Description |
| --- | --- | --- |
| `GET` | `/api/sales/orders` | Orders placed by the authenticated customer |
| `GET` | `/api/sales/orders/{id}` | One order owned by the authenticated customer |
| `POST` | `/api/sales/checkout` | Convert the customer's cart into an order |

All operations require `auth:sanctum`.

## Checkout

```json
{
  "cartToken": "8f0e…",
  "currencyCode": "USD",
  "gateway": "bank-transfer",
  "paymentReference": "TRF-8891",
  "cardMessage": "Happy birthday.",
  "recipientName": "Nasrin K.",
  "addressId": 7,
  "latitude": 35.7219,
  "longitude": 51.3347,
  "deliveryDate": "2026-10-02",
  "deliverySlotId": 3
}
```

Prices are never taken from the request. The processor reads the product name,
price and stock from `misaf/vendra-product` and snapshots them onto the order,
so a client cannot dictate what it pays. The same holds for delivery: the fee
comes from the `misaf/vendra-delivery` band the dropped pin falls in, and the
delivery is scheduled on the order. An address that band prices by hand is
refused rather than charged a guessed fee. Without a pin the order is placed
with a zero delivery amount and nothing is scheduled. `addressId` must belong
to one of the caller's own profiles.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-api`
- `misaf/vendra-delivery`
- `misaf/vendra-order`
- `misaf/vendra-product`

## Installation

```bash
composer require misaf/vendra-order-api
```

The service provider registers the resources and processors automatically.

Checkout locks the cart before loading its current items and commits the order, cart clearing, and delivery together. Invalid delivery slots, addresses, or booking dates leave the cart intact. Products must belong to an active category, matching catalog visibility.

## Testing

```bash
php artisan test --compact --testsuite=vendra-order-api
```

## License

MIT. See [LICENSE](LICENSE).
