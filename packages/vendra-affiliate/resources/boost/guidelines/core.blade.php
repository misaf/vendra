## Vendra Affiliate

The `misaf/vendra-affiliate` package owns affiliate and referral management with commission tracking and the Filament admin UI for affiliates and affiliate users.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.
- Store rules a store administrator may change (the default commission percent and signup bounty, which conversions earn commission, commission auto-approval, the referral cookie lifetime and redirect URL, and the payout minimum) live in `Settings\AffiliateSettings` (group `affiliate`, tenant repository), registered with `RegistersSettings` in the service provider and seeded as the platform default by `database/settings`. `Filament\Pages\ManageAffiliateSettings` edits them in the admin System cluster; read them with `resolve(AffiliateSettings::class)`, never from config. The class implements `ShouldLogActivity`, so each change is logged with its old and new values.

- Keep affiliate domain code inside `packages/vendra-affiliate` using the `Misaf\VendraAffiliate` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, observers, Filament resources, translations, config, and package bootstrapping.
- Follow the concrete models and neighboring files in this package; do not apply translation, media, slug, sorting, or soft-delete patterns unless the affected model already uses them.
- Keep `ProcessAffiliatePayoutAction` atomic: consuming commissions, creating the payout, and creating **and approving** the Commission transaction (which settles the wallet via the transaction module's ledger) happen in one database transaction. A payout is `Completed` only after its transaction is approved; on failure everything rolls back and commissions stay `Approved` and payable. The missing-user branch records a `Failed` payout without consuming commissions.
- Change a commission's status from the admin panel only through `Actions\ApproveAffiliateCommissionAction` (Pending → Approved) and `Actions\ReverseAffiliateCommissionAction` (Pending or Approved → Reversed). Both lock the row and recheck the status, so a commission paid out in the meantime is never reversed.
- Conversions (deposit and checkout) resolve the referrer through `Models\AffiliateReferral::forUser()` and credit through `Actions\CreditCommissionAction`; do not repeat the referral lookup.
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant` anywhere — models, migrations, factories, seeders, or fixtures. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep Filament resources thin by delegating forms to `Schemas/*Form.php` and tables to `Tables/*Table.php`.
- Because the package resources declare a `$cluster`, keep their complete resource trees under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraAffiliate\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a cluster must instead live under `src/Filament/Resources/`.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Tag-consuming models must use `Misaf\VendraSupport\Capabilities\HasOptionalTags` as the single source of their `tags()` relationship and pivot metadata. Keep the package tag-agnostic: define a stable package-owned tag type, use `TagIntegration` for availability and UI integration, never import the concrete Vendra Tagger model/provider or define the relationship through Spatie `HasTags`, and keep Tagger in Composer `suggest` rather than `require`.
- Add or update Pest tests for policy coverage, config/navigation behavior, translation parity, model contracts, and user-visible Filament behavior.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code. Do not duplicate coverage a focused test already proves, and do not add throwaway verification scripts when a test fits.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraAffiliate')->not->toUse('Misaf\VendraTenant')`.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.
