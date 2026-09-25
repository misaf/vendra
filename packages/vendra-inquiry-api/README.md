# Vendra Inquiry API

API Platform resource for the Vendra Inquiry module: the storefront contact
form.

## Endpoint

| Method | URI | Description |
| --- | --- | --- |
| `POST` | `/api/support/inquiries` | Send a contact enquiry to the studio |

```json
{
  "name": "Nasrin K.",
  "email": "nasrin@example.com",
  "message": "Two weddings in Mordad \u2014 do you still have dates?",
  "phone": "+98 21 8877 0134",
  "occasion": "wedding",
  "preferredLocale": "fa"
}
```

Anyone may write to a shop, so the endpoint is unauthenticated and throttled to
10 requests a minute. It answers `204`: an enquiry is inbox material for the
studio, not a resource the sender reads back. The source and the sender's
locale are taken from the request rather than the body, so neither can be
spoofed. An `occasion` must be one of the store's occasion slugs
(`Misaf\VendraInquiry\Settings\InquirySettings`); anything else answers `422`.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-api`
- `misaf/vendra-inquiry`

## Installation

```bash
composer require misaf/vendra-inquiry-api
```

The service provider registers the resources and processors automatically.

## Testing

```bash
php artisan test --compact --testsuite=vendra-inquiry-api
```

## License

MIT. See [LICENSE](LICENSE).
