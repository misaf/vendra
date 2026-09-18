# Vendra Delivery API

API Platform resources for the Vendra Delivery module: the storefront reads the
delivery bands, asks what a dropped pin costs, and lists the dates and windows a
customer can still choose.

## Endpoints

| Method | URI | Description |
| --- | --- | --- |
| `GET` | `/api/delivery/zones` | Active delivery bands, tightest first |
| `GET` | `/api/delivery/zones/{id}` | One active band |
| `GET` | `/api/delivery/schedule` | Bookable dates and delivery windows |
| `POST` | `/api/delivery/quotes` | Price a latitude/longitude |

## Quoting a pin

```json
{ "latitude": 35.7219, "longitude": 51.3347, "currencyCode": "USD" }
```

```json
{
  "zoneId": 2,
  "zoneName": { "en": "Outside the free zone" },
  "distanceKm": 6.42,
  "feeAmount": 1500,
  "currencyCode": "USD",
  "requiresQuote": false
}
```

Quoting writes nothing and reserves nothing, so a storefront may call it on
every drag of the map pin. A `requiresQuote` answer means the studio prices that
address by hand: checkout will refuse it rather than invent a fee.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-api`
- `misaf/vendra-delivery`

## Installation

```bash
composer require misaf/vendra-delivery-api
```

The service provider registers the resources and processors automatically.

## Testing

```bash
php artisan test --compact --testsuite=vendra-delivery-api
```

## License

MIT. See [LICENSE](LICENSE).
