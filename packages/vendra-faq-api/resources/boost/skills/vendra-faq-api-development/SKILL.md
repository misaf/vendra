---
name: vendra-faq-api-development
description: "Create, modify, review, or test the Vendra FAQ API module in packages/vendra-faq-api. Use for ApiResource DTOs, State providers, query parameters, API Platform operations, filters, pagination, FAQ and multimedia relationships, API tests, and package service provider wiring."
---

# Vendra FAQ API

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-faq-api` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

## Module Boundary

Treat `packages/vendra-faq-api` as the API Platform layer for `misaf/vendra-faq`.

- Use namespace `Misaf\VendraFaqApi`.
- Keep API resource DTOs, state providers, query parameters, service providers, and API tests inside this module.
- Import domain models from `Misaf\VendraFaq`; do not duplicate domain models or persistence logic in the API module.
- Keep production API code tenant-provider agnostic: inherit tenancy from the domain models and add no API tenant toggle or `Misaf\VendraTenant` reference in resources, state providers, or query parameters. Feature tests may use a concrete tenant factory solely to establish tenant context; keep the architecture rule scoped to `Misaf\VendraFaqApi`.
- Keep Filament/admin UI out of this module.
- Keep dependencies explicit in `composer.json`; do not add or change package dependencies without approval.

## API Platform Shape

Expose read models as API Platform resources in `src/ApiResource` (`FaqResource`, `FaqCategoryResource`), served by the shared `Misaf\VendraApi\State\EloquentResourceProvider`, with each resource's query in a `src/State` links handler (for example `FaqLinksHandler`) and its serialization in a mapper (for example `FaqMapper`).

- Define each resource as a `final readonly` DTO annotated with `#[ApiResource]`, declaring `Get`/`GetCollection` operations with explicit `uriTemplate` paths and a `provider`.
- Keep each resource `shortName` and URI path stable and kebab-case, for example `/content/faqs`, `/content/faq-categories`.
- Reference related resources, including multimedia, with `Misaf\VendraApi\ApiResource\ResourceReference` instead of embedding foreign models.
- Register the `src/ApiResource` directory into `api-platform.resources` and tag each state provider as `ProviderInterface` in the module service provider.
- Keep resources read-only unless a mutation is an explicit product decision.

## Resource DTO Standards

Resource DTOs define the public API contract.

- Mark the identifier property with `#[ApiProperty(identifier: true)]` and give every property an explicit type.
- Expose translated columns as `array<string, string>` locale maps such as `title` and `description`.
- Keep raw foreign keys and internal columns off the DTO; expose only intentionally public fields.
- Do the Eloquent querying, hydration, filtering, and pagination in the state provider, not the DTO.

## Filter And Query Standards

Declare supported query parameters on the resource operation.

- Add each filter as a `QueryParameter` with an API Platform filter (`EqualsFilter`, `BooleanFilter`, ...) and Laravel `constraints`.
- Set each parameter's `property` to the model column the state provider filters on.
- Apply the declared parameters inside the state provider when building the Eloquent query.
- For translated column filters, filter on the active-locale JSON path.

## Service Provider And Routes

Keep package bootstrapping minimal and predictable.

- Use the module `ServiceProvider` to register `src/ApiResource` into `api-platform.resources` and tag the state providers; API Platform generates routes from the resource operations.
- Do not hand-register route files for resource endpoints.
- Keep routes localization-package agnostic; locale-aware providers read Laravel's current locale, which the host application may resolve by any mechanism.

## Testing And Verification

Use Pest tests to protect API contracts.

- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts (or `tinker`) when a test fits.
- Add tests for every new resource operation and its query parameters.
- Add tests when resource shape, `shortName`, or URI paths change.
- Add tests when query parameters, filters, or pagination change.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus an expectation that the module stays tenant-agnostic, e.g. `arch()->expect('Misaf\VendraFaqApi')->not->toUse('Misaf\VendraTenant')`. The API module may depend on `Misaf\VendraFaq`, but not on any concrete tenant provider.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-faq-api` and `composer stan`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent` from the host app.
