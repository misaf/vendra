## Vendra Order API

The `misaf/vendra-order-api` package owns API Platform resources (`ApiResource` DTOs), state providers and processors, and service-provider wiring for `misaf/vendra-order`.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Keep order API code inside `packages/vendra-order-api` using the `Misaf\VendraOrderApi` namespace.
- Keep order models, migrations, factories, policies, seeders, states, and Filament UI in `misaf/vendra-order`; this package only serializes, routes, and composes them.
- Expose `OrderResource` and `OrderLine` under `/sales/orders` read-only, and checkout under `/sales/checkout`.
- Keep every operation authenticated with `middleware: 'auth:sanctum'` and a `policy` enforced by `CustomerOrderPolicy`; the collection and item queries are scoped to the caller in `OrderLinksHandler`.
- Never accept money from the client. `PlaceOrderProcessor` reads names, prices, and stock from `misaf/vendra-product` and hands `OrderLineDraft` values to `PlaceOrderAction`; the client only chooses the cart, currency, gateway, payment reference, card message, and delivery details (recipient, own address, pin, date, slot).
- Never accept a delivery fee from the client. `PlaceOrderProcessor` prices the dropped pin through `misaf/vendra-delivery`'s `DeliveryZoneMatcher`, refuses a pin that band prices by hand, and schedules the delivery with `ScheduleDeliveryAction`; without a pin the order carries a zero delivery amount and nothing is scheduled. Zones, slots, and fees stay in `misaf/vendra-delivery`.
- Keep the order customer morph columns private (`customerType`, `customerId`); do not serialize raw `customer_type` or `customer_id`.
- Reject unsupported sellable types, missing prices, out-of-stock products, and unavailable gateways with a validation error rather than a partial order.
- Do the Eloquent querying, hydration, and pagination in the state provider, not the DTO.
- Rely on the order models' support-layer tenant scope and keep production API code free of `Misaf\VendraTenant`. Feature tests may use a concrete tenant factory solely to establish tenant context.
- Keep user-facing processor errors in `vendra-order-api::messages` and update `en`, `de`, and `fa` together.
- Keep Pest architecture tests and focused resource/state-provider/policy tests current.

- Checkout locks the cart before loading its current items and commits the order, cart clearing, and delivery together. Invalid delivery slots, addresses, or booking dates leave the cart intact. Products must belong to an active category, matching catalog visibility; eligibility and unit prices come from `ProductPurchaseQuoter` in `vendra-product`, which loads the whole cart's products in one query. Checkout then takes the ordered stock through `DeductProductStockAction` in the same transaction, refusing the cart with `out_of_stock` if another checkout took it first, and records `stock_deducted` on the order. `Listeners\RestockCancelledOrder` returns the stock when the order is cancelled, only for orders with that flag, and clears it afterwards.
