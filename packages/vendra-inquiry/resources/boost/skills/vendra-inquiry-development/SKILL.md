---
name: vendra-inquiry-development
description: "Create, modify, review, or test the Vendra Inquiry module in packages/vendra-inquiry. Use for Inquiry, contact enquiries, the storefront contact form, SubmitInquiryAction, InquiryStatusEnum, answering, closing and reopening enquiries, inquiry migrations and factories, inquiry policies and permission seeders, the vendra-inquiry:seed command, InquiryPlugin, CustomersCluster, InquiryResource, translations, or configuration."
---

# Vendra Inquiry

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-inquiry` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

Use this skill with `laravel-best-practices` for Laravel PHP, `pest-testing` when tests change, and `vendra-permission-development` when policies or permissions change. Use `tailwindcss-development` only for Blade or Tailwind UI.

## Module Boundary

Treat `packages/vendra-inquiry` as the source of storefront contact-enquiry behavior and its Filament inbox.

- Use namespace `Misaf\VendraInquiry`.
- Keep models, factories, migrations, policies, permission enums, seed commands, Filament classes, config, translations, and tests inside this module.
- Keep orders, carts, deliveries, catalog, and customer accounts outside this module. An enquiry is a message, not a transaction.
- Keep API schemas and routes in `misaf/vendra-inquiry-api`.
- Keep dependencies explicit and request approval before introducing new packages.

## Domain Standards

- Model an enquiry with a name, email, optional phone, optional occasion slug, verbatim message, `InquiryStatusEnum` status, optional source and locale, optional metadata, and an `answered_at` stamp.
- Default a new enquiry to `InquiryStatusEnum::New` and badge the inbox on that scope.
- Validate at the caller (e.g. `misaf/vendra-inquiry-api`'s `SubmitInquiryRequest`) before calling `SubmitInquiryAction`; the action does not validate.
- Keep status changes on the model (`markAnswered()`, `close()`, `reopen()`); they are single writes and need no action wrapper.
- Use typed Eloquent relationships with PHPDoc generics, Laravel model attributes, explicit casts, final classes, and `declare(strict_types=1)`.

## Tenant Awareness

- Derive tenancy only from `misaf/vendra-support`; never reference `Misaf\VendraTenant`.
- Apply `BelongsToTenant` to `Inquiry` and use `TenantSchema` in its migration.
- Never assign `tenant_id` manually or add a `tenant_aware` configuration value.
- Keep factories and permission seeders functional with tenancy enabled or disabled.

## Filament And Permissions

- Register `InquiryResource` through `InquiryPlugin` and `InquiryServiceProvider`, respecting configured panel IDs.
- Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraInquiry\Filament\Clusters\Resources` namespace and plugin registration. Resources without a cluster belong under `src/Filament/Resources/`; delegate schemas and tables to dedicated classes.
- Keep the inbox read-and-triage only: enquiries arrive from the storefront, so `create` is denied.
- Guard each status action on the current status so an already-answered enquiry cannot be answered twice.
- Use Filament v5 namespaces: fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, columns from `Filament\Tables\Columns`, actions from `Filament\Actions`, and icons from `Filament\Support\Icons\Heroicon`.
- Use `vendra-inquiry::attributes`, `vendra-inquiry::navigation`, `vendra-inquiry::enums`, and `vendra-inquiry::messages` translation keys for every visible label.
- Keep `InquiryResource` ungrouped and assign `$navigationSort` from `NavigationPriority::Inquiries`; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Keep policy methods aligned with exposed actions and backed by `InquiryPolicyEnum` permissions.
- Update `PermissionPolicySeeder` and `vendra-inquiry:seed` whenever permission cases change.

## Data And Localization

- Keep the package migration reversible and index status, email, occasion, and arrival time within the tenant boundary.
- Store the sender's locale so a reply can be written in the language they wrote in.
- Update English, German, and Persian locale files together, with sorted keys and parity.

## Testing And Verification

- Add focused Pest tests for submission validation, defaults, status transitions, migration constraints, policy permissions, plugin/resource registration, and tenant independence.
- Add Livewire tests when Filament interaction behavior changes materially.
- Run `php artisan test --compact packages/vendra-inquiry/tests`.
- Run PHPStan against the module source, factories, and seeders.
- Run `vendor/bin/pint --dirty --format agent` after changing PHP files.
