---
name: vendra-affiliate-api-development
description: "Create, modify, review, or test the Vendra Affiliate API package in packages/vendra-affiliate-api. Use for the public affiliate API Platform resources (AffiliateResource, AffiliateClickResource `ApiResource` DTOs), State providers/processors, query parameters, active-affiliate visibility, filters, operations, response fields, API tests, and AffiliateApiServiceProvider wiring."
---

# Vendra Affiliate API

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-affiliate-api` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

Use `vendra-api-development` for shared API Platform infrastructure, `laravel-best-practices` for Laravel PHP and `pest-testing` when tests change. Before editing, inspect the installed versions with Laravel Boost and search the version-specific documentation for the affected API Platform behavior.

## Boundary And Contract

- Work inside `packages/vendra-affiliate-api` with namespace `Misaf\VendraAffiliateApi`.
- Reuse `Misaf\VendraAffiliate\Models\Affiliate`; do not duplicate affiliate persistence, status transitions, commissions, referrals, or payouts.
- Preserve the public contract: referral-code lookup is read-only, exposing active affiliates only and serializing just `code` and `created_at`. The sole write is the throttled, validated referral-visit endpoint that delegates to the domain action and returns `204` with no output.
- Apply active status in `AffiliateLinksHandler`, the query every operation shares through `EloquentResourceProvider`, so index, filter, and show operations cannot diverge.
- Keep filters intentionally narrow and never expose user relationships or financial fields.
- Inherit tenant scoping from the domain model and keep the production `Misaf\VendraAffiliateApi` namespace free of `Misaf\VendraTenant`. Feature tests may use a concrete tenant factory solely to establish tenant context.

## Change Checklist

- Update the `#[ApiResource]` DTOs, State providers/processors, query-parameter and `FormRequest` validation, and operation routes together when the contract changes.
- Add focused Pest coverage for public fields, active/suspended visibility, filtering, pagination, the referral-visit write, and operation routes.
- Preserve the architecture expectation that `Misaf\VendraAffiliateApi` does not use `Misaf\VendraTenant`.
- Run `php artisan test --compact --testsuite=vendra-affiliate-api` and `composer stan`; run Pint when PHP changes.
