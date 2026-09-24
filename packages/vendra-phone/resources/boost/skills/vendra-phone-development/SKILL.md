---
name: vendra-phone-development
description: "Create, modify, review, or test the optional Vendra Phone provider in packages/vendra-phone, including international E.164 user-profile numbers and Filament Phone Input integration."
---

# Vendra Phone

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-phone` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep this package independently installable and coupled only one-way to `vendra-user-profile`.
- Register `phoneNumbers` dynamically and contribute UI through `UserProfileRelationManagers`.
- Keep `PhoneNumberPolicy`, `PhoneNumberPolicyEnum`, and `PermissionPolicySeeder` aligned for Filament strict authorization.
- Use `ysfkaya/filament-phone-input`, store ISO country separately with `countryStatePath()`, and store the number in E.164 format.
- Phone fields are scalar and non-translatable; JSON metadata is only for country/carrier-specific structured information.
- Each user profile has exactly one primary phone number (`is_primary`), kept by the synchronous `Observers\PhoneNumberObserver`, which uses vendra-support's `MaintainsSingleFlagPerOwner`.
- Never reference a concrete tenant provider.
