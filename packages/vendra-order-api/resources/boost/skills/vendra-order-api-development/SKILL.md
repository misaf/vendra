---
name: vendra-order-api-development
description: "Create, modify, review, or test the Vendra Order API module in packages/vendra-order-api. Use for OrderResource, OrderLine, CheckoutResource, PlaceOrderProcessor, OrderMapper, OrderLinksHandler, CustomerOrderPolicy, the /sales/orders and /sales/checkout endpoints, order MCP tools, checkout validation rules, or API serialization of orders."
---

# Vendra Order API

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-order-api` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

Use this skill with `laravel-best-practices` for Laravel PHP and `pest-testing` when tests change.

## Module Boundary

Treat `packages/vendra-order-api` as the HTTP and MCP surface of `misaf/vendra-order`.

- Use namespace `Misaf\VendraOrderApi`.
- Keep DTOs in `src/ApiResource`, providers/processors/mappers in `src/State`, validation rules in `src/Http/Requests`, and authorization in `src/Policies`.
- Keep order persistence, lifecycle states, permissions, and administration in `misaf/vendra-order`.
- Keep catalog ownership in `misaf/vendra-product`; read from it, never write to it.
- Keep delivery zones, slots, and fees in `misaf/vendra-delivery`.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Serialize amounts as integers in minor units alongside the order's `currencyCode`.
- Serialize the order status as its state morph name, not a class name.
- Serialize each line's localized `name` snapshot; never re-read the current catalog name for a placed order.
- Resolve prices, names, and stock inside `PlaceOrderProcessor`; the request carries no money.
- Throw `ValidationException` for an unknown cart, an empty cart, an unsupported sellable type, a missing price, insufficient stock, or an unavailable gateway.
- Delegate the write itself to `PlaceOrderAction` so the whole conversion stays in one transaction.
- Use final classes, readonly DTOs, explicit types, PHPDoc generics, and `declare(strict_types=1)`.

## Testing And Verification

- Cover ownership scoping, unauthenticated access, successful checkout, and each validation failure.
- Assert that a placed order snapshots the catalog price rather than any client-supplied value.
- Run `php artisan test --compact packages/vendra-order-api/tests`.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.

- Checkout locks the cart before loading its current items and commits the order, cart clearing, and delivery together. Invalid delivery slots, addresses, or booking dates leave the cart intact. Products must belong to an active category, matching catalog visibility; eligibility and unit prices come from `ProductPurchaseQuoter` in `vendra-product`, which loads the whole cart's products in one query.
