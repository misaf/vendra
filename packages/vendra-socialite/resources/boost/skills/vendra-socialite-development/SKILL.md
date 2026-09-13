---
name: vendra-socialite-development
description: "Create, modify, review, or test the Vendra Socialite module in packages/vendra-socialite. Use for OAuth social login, filament-socialite wiring, the `SocialiteUser` model, `SocialiteRegistrar`, the socialite_users migration, provider (Google/GitHub) configuration, and the socialite service provider / plugin registration."
---

# Vendra Socialite

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-socialite` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

Use `socialite-development` for OAuth flows, `laravel-best-practices` for Laravel PHP and `pest-testing` when tests change. Before editing, inspect the installed package versions and search Laravel Boost documentation for Socialite and Filament integration behavior.

## Module Boundary

`packages/vendra-socialite` is the optional social-login add-on for Vendra. It depends on `misaf/vendra-user` but `misaf/vendra-user` must never depend on it — requiring this package alone enables social login.

- Use namespace `Misaf\VendraSocialite`.
- Keep the `SocialiteUser` model, `create_socialite_users_table` migration, `Support\SocialiteRegistrar`, config, and `SocialiteServiceProvider` inside this module.
- Do not reintroduce socialite code, config, dependencies, or the `socialite_users` schema into `misaf/vendra-user`.

## Domain Standards

- `SocialiteUser` extends `dutchcodingcompany/filament-socialite`'s base model and adds `BelongsToTenant`, overriding `findForProvider`/`createForProvider` (the base uses `self`, which bypasses the tenant scope).
- Derive tenancy from `misaf/vendra-support` (`BelongsToTenant`, `TenantSchema`), never `Misaf\VendraTenant`.
- Register the `filament-socialite` plugin on the panels in `config/vendra-socialite.php` through `SocialiteServiceProvider`; build the plugin in `SocialiteRegistrar::make()`.
- Map OAuth accounts onto the `misaf/vendra-user` `username`-based schema in `SocialiteRegistrar::createUserUsing` (unique username, verified email, random password), creating the user through `vendra-user`'s `CreateUserAction` with the current tenant from `TenantResolver`.
- Provider OAuth credentials belong in the host `config/services.php` (`google`, `github`), not in this module.

## Testing

- Cover the `SocialiteUser` contract/tenant-ownership, `SocialiteRegistrar` username derivation and user creation, and keep the arch presets plus `not->toUse('Misaf\VendraTenant')` in `tests/ArchTest.php`.
- Run module checks from the repo root: `php artisan test --compact --testsuite=vendra-socialite`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
