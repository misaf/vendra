---
name: vendra-cart-api-development
description: "Create, modify, review, or test the Vendra Cart API Platform package in packages/vendra-cart-api. Use for the CartResource and CartLine API Platform resources (`ApiResource` DTOs), CartLinksHandler and CartMapper state classes, query parameters, operations, authentication and ShoppingCartPolicy, CartApiServiceProvider, cart API serialization, or decisions about exposing polymorphic owners and sellables through API Platform."
---

# Vendra Cart API

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-cart-api` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

Use this skill with `vendra-api-development`, `laravel-best-practices`, and `pest-testing` when tests change. Before code changes, use Laravel Boost `application-info` and `search-docs` for API Platform for Laravel.

## Boundary

- Keep API code in `packages/vendra-cart-api` with namespace `Misaf\VendraCartApi`.
- Depend on `misaf/vendra-cart` for models and `misaf/vendra-api` for shared API Platform infrastructure.
- Keep domain behavior, migrations, factories, policies, seeders, and Filament classes out of this package.
- Keep production API code free of `Misaf\VendraTenant`; model scopes provide tenant isolation. Feature tests may use a concrete tenant factory solely to establish tenant context.

## Resources And Routes

- Expose read models as API Platform resources in `src/ApiResource` (`CartResource`, `CartLine`), served by the shared `EloquentResourceProvider` with `CartLinksHandler` and `CartMapper` in `src/State`; API Platform generates routes from the resource operations.
- Keep the `/sales/carts` operations authenticated: attach `middleware: 'auth:sanctum'` and a `policy` enforced by `ShoppingCartPolicy`.
- Register the `src/ApiResource` directory into `api-platform.resources` and tag the state provider as `ProviderInterface`; do not hand-register route files.
- Keep the operations read-only until authenticated ownership/token access and mutation authorization have an explicit design.

## Resource DTO Standards

- Serialize cart token, expiration, and the lines relationship; keep the owner morph columns private (`ownerType`, `ownerId`) rather than exposing them.
- Serialize each line's sellable type/ID, quantity, and metadata via `CartLine`.
- Reference related resources with `Misaf\VendraApi\ApiResource\ResourceReference` where a full DTO is not exposed.
- Do the Eloquent querying, hydration, and pagination in the state provider, not the DTO.

## Verification

- Test each resource operation, its policy/authentication, resource attribute boundaries, and architecture constraints.
- Run `php artisan test --compact packages/vendra-cart-api/tests`.
- Run PHPStan against `packages/vendra-cart-api/src`.
- Run `vendor/bin/pint --dirty --format agent` after PHP changes.
