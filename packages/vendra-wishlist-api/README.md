# Vendra Wishlist API

API Platform resources for the Vendra Wishlist module: customers read their own
lists and toggle the heart on a product card.

## Endpoints

| Method | URI | Description |
| --- | --- | --- |
| `GET` | `/api/customers/wishlists` | Lists owned by the authenticated customer |
| `GET` | `/api/customers/wishlists/{id}` | One list owned by the customer |
| `POST` | `/api/customers/saved-items` | Save a product to the default list |
| `DELETE` | `/api/customers/saved-items/{id}` | Forget one saved item |

All operations require `auth:sanctum`.

## Saving

```json
{ "sellableType": "product", "sellableId": 12 }
```

Saving resolves the caller's default list, creating it on first use, and
answers with the whole list so the storefront can re-render every heart in one
round trip. Saving twice is not an error and does not duplicate the row.

An identifier that belongs to somebody else's list is indistinguishable from
one that does not exist: both answer `404`.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-api`
- `misaf/vendra-product`
- `misaf/vendra-wishlist`

## Installation

```bash
composer require misaf/vendra-wishlist-api
```

The service provider registers the resources and processors automatically.

## Testing

```bash
php artisan test --compact --testsuite=vendra-wishlist-api
```

## License

MIT. See [LICENSE](LICENSE).
