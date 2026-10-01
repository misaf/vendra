---
name: vendra-attribute-development
description: "Create, modify, review, or test the Vendra Attribute package in packages/vendra-attribute. Use for Attribute, AttributeValue, AttributableModel, HasAttributeValues, AttributeResolver wiring, configurable units, optional attribute tags, migrations, factories, policies, Filament resources and relation managers, translations, and package registration."
---

# Vendra Attribute

## Workflow

- The resolver applies `TenantAwareness::constrainToCurrentTenant()` even when a configured model has no tenant scope. With tenancy enabled, use the resolver-defined tenant column: a current tenant sees its own active attributes and a tenantless caller sees only platform rows. Unexpected failures are reported through Laravel before falling back; empty catalogs and missing tables remain quiet.

- Own the concrete `Misaf\VendraAttribute\Support\EloquentAttributeResolver` adapter here and bind it to Support's `AttributeResolver` contract. Preserve configurable attribute/value models, column names, active filtering, ordering, and fallback behavior. Consumers depend on the shared contract rather than this adapter.

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-attribute` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

## Domain Boundary

- Work inside `packages/vendra-attribute` with namespace `Misaf\VendraAttribute`.
- Keep attributes consumer-neutral. Add relationships to consumers through `HasAttributeValues`, not hard-coded Product or other domain dependencies.
- Preserve `AttributeValue` as a sortable polymorphic value belonging to one `Attribute` and one `attributable` model.
- Keep `AttributeResolver` bound to the package models through `AttributeServiceProvider`.
- Derive tenancy through `BelongsToTenant` and support-layer schema helpers; never import `Misaf\VendraTenant` or set `tenant_id` manually.
- Tag-consuming models must use `Misaf\VendraSupport\Capabilities\HasOptionalTags` as the single source of their `tags()` relationship and pivot metadata. Keep the package tag-agnostic: define a stable package-owned tag type, use `TagIntegration` for availability and UI integration, never import the concrete Vendra Tagger model/provider or define the relationship through Spatie `HasTags`, and list Tagger only under Composer `suggest`.
- Keep unit labels in `vendra-attribute.units` and normalize them through `AttributeUnits::options()`.

## Change Checklist

- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraAttribute\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.
- Keep `AttributeResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Attributes`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep resources thin, with form/table definitions and the value relation manager in their existing Filament directories.
- Update migrations, factories, policies, permission seeders, configuration, and translation parity when their contracts change.
- Cover polymorphic values, resolver wiring, units, optional tags, policies, and user-visible Filament behavior with focused Pest tests.
- Preserve architecture expectations against Product, Tenant, Vendra Tagger, and Spatie Tags.
- Run `php artisan test --compact --testsuite=vendra-attribute` and `composer stan`; run Pint when PHP changes.
