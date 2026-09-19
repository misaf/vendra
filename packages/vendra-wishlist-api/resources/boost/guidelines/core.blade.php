## Vendra Wishlist API

The `misaf/vendra-wishlist-api` package owns API Platform resources (`ApiResource` DTOs), state providers and processors, and service-provider wiring for `misaf/vendra-wishlist`.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Keep wishlist API code inside `packages/vendra-wishlist-api` using the `Misaf\VendraWishlistApi` namespace.
- Keep wishlist models, migrations, factories, policies, seeders, actions, and Filament UI in `misaf/vendra-wishlist`; this package only serializes and routes them.
- Expose `/customers/wishlists` read-only and `/customers/saved-items` for saving and forgetting.
- Keep every operation authenticated with `middleware: 'auth:sanctum'`; scope reads to the caller in `WishlistLinksHandler` and enforce `CustomerWishlistPolicy`.
- Resolve the target list through `Wishlist::firstOrCreateDefaultFor()` rather than accepting a list identifier: a heart button has no list to pick.
- Answer a save with the whole list so the storefront can re-render every heart in one round trip.
- Verify the saved sellable against the catalog before writing; reject an unknown identifier with a validation error rather than storing a dangling reference.
- Answer `404` for a saved item belonging to another customer, so an identifier reveals nothing about somebody else's list.
- Keep the wishlist owner morph columns private (`ownerType`, `ownerId`); do not serialize raw `owner_type` or `owner_id`.
- Give input DTOs with multi-word properties an explicit `#[SerializedName]`: the configured name converter otherwise maps camelCase wire names onto snake_case PHP properties and silently drops them.
- Do the Eloquent querying, hydration, and pagination in the state provider, not the DTO.
- Rely on the wishlist models' support-layer tenant scope and keep production API code free of `Misaf\VendraTenant`. Feature tests may use a concrete tenant factory solely to establish tenant context.
- Keep user-facing processor errors in `vendra-wishlist-api::messages` and update `en`, `de`, and `fa` together.
- Keep Pest architecture tests and focused resource/state-provider/policy tests current.
