---
name: vendra-support-development
description: "Create, modify, review, or test the Vendra Support module in packages/vendra-support, changing tenant awareness, optional capability resolvers, or shared Filament sidebar navigation. Use for TenantResolver, TagResolver, TagIntegration, NullTenantResolver, TenantAwareness, BelongsToTenant, TenantScope, TeamScope, TenantSchema, TenantTableRegistry, RequiresCurrentTenant, TenantSeeders, shared seeders and commands, Filament NavigationGroup taxonomy, navigation groups, package sidebar priority, package icons, clusters, sub-navigation, shared Filament concerns, shared events/listeners, ResolvesPolicyPermissions, the composable policy ability traits (Authorizes*Abilities), and permission policy enum conventions."
---

# Vendra Support

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-support` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

## Module Boundary

Treat `packages/vendra-support` as the shared support and tenant-awareness core.

- Use namespace `Misaf\VendraSupport`.
- Own the tenant abstraction here and nowhere else: the `TenantResolver` contract, the default `NullTenantResolver`, `TenantAwareness`, `BelongsToTenant`, `TenantScope`/`TeamScope`, `TenantSchema`, `TenantTableRegistry`, `RequiresCurrentTenant`, `TenantSeeders`, the base seeders and seed commands, `Context\RequestJobContext`, shared Filament concerns, and the shared policy authorization concerns (`ResolvesPolicyPermissions` plus the `Authorizes*Abilities` traits).
- Never depend on a concrete tenant provider (`Misaf\VendraTenant`) or any domain module. Support sits at the bottom of the dependency graph and must build and run standalone.
- Own small optional-provider boundaries here. Tag consumers use `TagResolver`, `TagIntegration`, and `TagRelationship`; the concrete Tagger module binds the available resolver. `SubscriptionCharger` (null default `NullSubscriptionCharger`) is the payment-collection capability — subscription consumers call it while the host app binds a real charger (e.g. backed by `misaf/vendra-transaction`). `SubscriptionCharge::reference` is an idempotency key: `charge()` / `retrieve()` must resolve repeat calls to the same provider operation without collecting twice, reject reuse for different details, and return typed `SubscriptionChargeResult` lifecycle outcomes. Never represent an ambiguous timeout or pending operation as paid.
- Keep `TagRelationship` limited to Eloquent polymorphic relationship metadata. Do not leak Spatie Tags or a domain model into Support.
- Keep the reusable consumer relation in `HasOptionalTags`; require each model to return a stable domain-specific type and keep unavailable-provider failure behavior consistent.

## Tenant Abstraction Rules

Tenant awareness is derived purely from the bound resolver — never from config.

- `TenantAwareness::enabled()` returns `app(TenantResolver::class)->available()`. The default `NullTenantResolver` reports unavailable, so tenancy is off until a provider binds a real resolver.
- `BelongsToTenant` adds `TenantScope` + `TeamScope` and a `creating` hook that stamps `tenant_id` only when `TenantSchema::hasTenantColumn()` and a tenant is current. Do not assign `tenant_id` manually anywhere.
- `TenantSchema::addTenantColumn` / `tenantIndex` add the column and composite indexes only when tenancy is enabled, so migrations stay valid with tenancy off.
- Register every domain table whose migration uses `TenantSchema::addTenantColumn()` with the shared `TenantTableRegistry` from that domain package's service provider. Preserve configurable table names and database connections in the registration.
- Keep `TenantTableRegistry` provider- and domain-agnostic. Domain packages declare only their own tables; a concrete tenant provider consumes the registry to retrofit schemas migrated while tenancy was disabled.
- Extend the contract deliberately: keep `TenantResolver` minimal and make sure both `NullTenantResolver` and any provider resolver implement every method.
- `RequiresCurrentTenant` offers `currentTenant()` (strict) and `currentTenantOrNull()` (null when tenancy is disabled) — pick the strict one only where a tenant is genuinely required.

## Shared Building Blocks

- Base `DemoContentSeeder` / `PermissionPolicySeeder` and `SeedCommand` / `TenantSeedCommand` must run tenant-agnostically: seed globally when tenancy is off, per-tenant (optional `{tenant?}` arg) when on.
- Keep `RequestJobContext` domain-neutral. Its first-class fields are trace ID, actor, operation, tenant, and a hidden idempotency key; domain packages supply their own visible metadata. Use `add()` for persistent current-process context, `scope()` for temporary context, `current()` to snapshot it, and `resolveTraceId()` to preserve or create correlation IDs.
- Keep shared Filament concerns generic and free of any single module's domain assumptions.
- `Filament\Forms\Components\IsActiveToggle` and `Filament\Tables\Columns\IsActiveToggleColumn` are the shared boolean active/inactive controls: default name `active`, Bolt on-icon, shared label; the form field is live, required, `boolean`-validated, and validates its own state path on change. Keep them domain-free — record-specific writes (`updateStateUsing`) and extra lifecycle conditions (`disabled`) stay at the call site. The column also stays disabled unless the panel user passes the record policy's `update` ability (models without a policy are left to their panel's access gate), because Filament never authorizes inline column updates on its own.
- `Filament\Infolists\Components\IsActiveEntry`, `Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint`, and `Filament\Tables\Filters\IsActiveFilter` default to `active` with the shared label; the filter uses Filament's default `where(column, true|false)` queries and the shared Active / Inactive option labels.
- `IsDefaultToggle`, `IsDefaultEntry`, `IsDefaultIconColumn` (Bolt true-icon), `IsDefaultFilter`, and `Filament\Tables\Filters\QueryBuilder\Constraints\IsDefaultConstraint` are the shared `is_default` controls: default name `is_default` and the shared label; the toggle mirrors `IsActiveToggle` but defaults to off. `IsPrimaryToggle` and `IsPrimaryIconColumn` are the `is_primary` counterparts. Choosing which record is the default stays in the owning package's action.
- `Observers\Concerns\MaintainsSingleActiveDefault` owns the single-active-default rule for `Currency`, `Language`, `Plan`, and `TransactionGateway`. Observers use the trait rather than reimplementing it; the fallback default follows the model's `ordered` scope when it has one, otherwise its key, and a soft-deleted default gives up the flag.
- `Observers\Concerns\MaintainsSingleFlagPerOwner` owns the one-flagged-row-per-owner rule for `Address` (`is_default`), `PhoneNumber` (`is_primary`) and `UserProfile` (`is_default`). An observer only names `flagColumn()` and `ownerColumn()`; the owner's first row takes the flag, the only flagged row cannot be unflagged, deleting it hands the flag to the oldest remaining row, and a soft-deleted row gives it up.
- `Filament\Tables\Columns\CreatedAtColumn`, `UpdatedAtColumn`, and `DeletedAtColumn` extend the abstract `TimestampColumn` (shared `vendra-support::attributes.*` label, LTR cell, since-tooltip, `Y-m-d H:i`, Jalali under `fa`; `DeletedAtColumn` adds a `—` placeholder); `RowIndexColumn` is the `#` row index sorted by `id`. Per-table choices such as sortability, badges, or a `position` sort stay at the call site.
- `Filament\Infolists\Components\CreatedAtEntry` and `UpdatedAtEntry` extend the abstract `TimestampEntry` (Jalali `Y-m-d H:i` under `fa`, otherwise `dateTime('Y-m-d H:i')`) with the shared labels; `DateTimeEntry` is the unlabelled variant for other timestamp attributes. `IsActiveIconColumn` (Bolt true-icon) and the `PositionConstraint`, `NameConstraint`, and `SlugConstraint` query-builder constraints carry only the default name and shared label.
- `SluggableNameInput` and `SlugInput` are live-on-blur, required, 255-character, half-width inputs that validate their own state path; both expose `uniqueWithinTenant(bool $perLocale = false)` from `Filament\Concerns\ValidatesUniquenessWithinTenant`, which scopes the rule through `TenantAwareness`, ignores trashed rows, and targets `<column>-><active form locale>` when `$perLocale` is true. `DescriptionTextarea` is the live, full-width description field; `NameEntry`, `SlugEntry`, `DescriptionEntry`, and `SlugColumn` carry only the shared label and presentation. Keep them domain-free.
- `Filament\Tables\Columns\NameColumn` (Tag icon, start-aligned) and `DescriptionColumn` (DocumentText icon, hidden by default) carry only the shared label and presentation; `searchable()`, `sortable()`, and translated `state()` callbacks stay at the call site. `Filament\Forms\Components\DescriptionRichEditor` is the required, full-width, `json()` rich-text description field.

## Policy Authorization Concerns

Domain policies across all modules compose the shared ability traits instead of hand-writing per-ability methods.

- `Concerns\ResolvesPolicyPermissions` declares abstract `permissionEnum(): string` (the module's `*PolicyEnum`) and resolves enum cases by ability name; a composed ability with no matching case throws at call time — keep that fail-loud behavior.
- The ability traits are `AuthorizesViewAbilities` (view, viewAny), `AuthorizesCreateAbilities`, `AuthorizesUpdateAbilities`, `AuthorizesDeleteAbilities` (delete, deleteAny), `AuthorizesForceDeleteAbilities`, `AuthorizesRestoreAbilities`, `AuthorizesReplicateAbilities`, and `AuthorizesReorderAbilities`.
- Compose whole traits only. Never adopt a trait whose abilities the resource does not grant; hard denies (`return false`) and model-state rules stay explicit class methods, and custom abilities (approve, send, process) call `$this->allowed($user, 'Case')` directly.
- Policy permission enums use TitleCase cases named exactly after the ability (`ViewAny`, `ForceDelete`) with kebab-case permission values (`view-any-attribute`); the resolver matches case names to abilities, so names must not drift.
- Every policy also keeps `use AuthorizesSandboxMode;` — the root `PackagePolicySandboxModeTest` enforces it.

## Filament Navigation Architecture

Use `Misaf\VendraSupport\Filament\Navigation\NavigationGroup` as the single source of navigation group labels and `NavigationPriority` as the single source of resource ordering. Do not add app translation strings as package group defaults.

- Register groups in the admin panel as `Filament\Navigation\NavigationGroup` objects with label closures. Do not pass the enum class directly: the locale middleware runs after panel construction, so eager labels can be cached in the wrong locale.
- Store package resources that declare a `$cluster` under `src/Filament/Clusters/Resources/` with matching `Filament\Clusters\Resources` namespaces. Store resources without a cluster under `src/Filament/Resources/`, and keep plugin discovery paths aligned.
- Keep group order: Catalog, Sales, Customers, Content, Marketing, Localization, System.
- Keep current item order:
  - Catalog: Products 1, Product Categories 2, Product Prices 3, Attributes 4.
  - Sales: Transactions 1, Transaction Gateways 2, Wallets 3, Currencies 4, Carts 5.
  - Customers: Users 1, User Profiles 2, Roles 3, Permissions 4.
  - Content: Blog Posts 1, Blog Post Categories 2, Custom Pages 3, Custom Page Categories 4, FAQs 5, FAQ Categories 6, Multimedia 7, Tags 8.
  - Marketing: Affiliates 1, Affiliate Commissions 2, Affiliate Payouts 3, Newsletters 4, Newsletter Subscribers 5.
  - Localization: Languages 1, Language Lines 2.
  - System: General Settings 1, Activity Logs 2.
- Give every resource a globally unique `NavigationPriority` case and assign `$navigationSort` from its backed value. Group values by domain cluster and leave gaps for future resources.
- Give every resource separate singular and plural translation keys in `en`, `de`, and `fa`. Use the singular key for model labels and the plural key for navigation and plural model labels; keep navigation labels at 24 characters or fewer.
- Use domain clusters as top-level sidebar items, set `$subNavigationPosition = SubNavigationPosition::Top`, and keep cluster resources ungrouped so `NavigationPriority` controls their visible tab order.
- Set one distinct outlined `Heroicon` on every top-level cluster and resource. Keep navigation group headers icon-free.
- Preserve package plugin `navigationGroup(...)` overrides and default them to `vendra-support::navigation.groups.*`.
- Use the package label for its cluster breadcrumb. Never use the broad group label as the breadcrumb.
- Update `tests/Feature/AdminNavigationTest.php` for group order, locale-safe labels, item sort, icons, and top sub-navigation.
- Verify the rendered sidebar in at least one non-default locale and open each new destination. Do not expose incomplete or failing package UI.

## Testing And Verification

- Keep tests purposeful: cover the resolver-derived tenant contract, scopes, schema helpers, table registration, and base seeder/command behavior with tenancy both on and off.
- Keep the root fresh-schema registry test aligned so every application table containing `tenant_id`, except tenant-provider-owned relationship tables, is registered for legacy schema retrofits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus `arch()->expect('Misaf\VendraSupport')->not->toUse('Misaf\VendraTenant')` — support must never couple to a concrete provider.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-support` and `composer stan`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.
