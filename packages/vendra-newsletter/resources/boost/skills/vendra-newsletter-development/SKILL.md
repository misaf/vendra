---
name: vendra-newsletter-development
description: "Create, modify, review, or test the Vendra Newsletter domain/admin module in packages/vendra-newsletter. Use for Newsletter, NewsletterSubscriber, subscriber restoration, newsletter delivery receipts and idempotency, the newsletter status enum, migrations, factories, seeders, policies, permission enums, Filament resources/clusters/forms/tables, the Filament Send action, the send pipeline (SendNewsletterAction action, SendNewsletterBatchJob, SendNewsletterEmailJob, NewsletterMail), queue/batch/Horizon timeout configuration, the public unsubscribe controller/route, the scheduled send command and its config-driven per-tenant schedule, translations, and plugin/service provider wiring."
---

# Vendra Newsletter

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-newsletter` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

## Module Boundary

Treat `packages/vendra-newsletter` as the source of newsletter domain behavior, the Filament admin UI, the send pipeline, the unsubscribe flow, and the scheduled send.

- Use namespace `Misaf\VendraNewsletter`.
- Keep domain models, factories, seeders, policies, mail, jobs, actions, HTTP controllers/routes, console commands, Filament classes, config, migrations, translations, views, and tests inside this module.
- Do not place newsletter domain code in the host app unless the host app is only integrating the module.
- Keep cross-module dependencies explicit in `composer.json`; do not introduce a dependency without approval.

## Domain Model Standards

Follow the existing `Newsletter` and `NewsletterSubscriber` patterns for new newsletter entities.

- Use `declare(strict_types=1)`, final classes, typed method signatures, and PHPDoc generics for query scopes and relationships.
- Prefer Laravel attributes already used here: `#[Fillable]`, `#[Hidden]`, and `#[UseFactory]`.
- Keep the module tenant-agnostic: derive tenant awareness purely from the bound `TenantResolver` in `misaf/vendra-support` (`TenantAwareness`, `BelongsToTenant`, `TenantSchema`, `RequiresCurrentTenant`, `eachTenant`). Never reference a concrete provider such as `Misaf\VendraTenant`, `Tenant::`, or the `tenants:artisan` command. There is no `tenant_aware` config toggle.
- Hide `tenant_id` and keep tenant behavior centralized in the support layer; `BelongsToTenant` assigns `tenant_id` on `creating` from the current tenant.
- Hide generated secrets such as `unsubscribe_token`, and assign them from a model `creating` hook rather than the form.
- Keep subscriber email unique per tenant and `unsubscribe_token` globally unique. Route creation through `Actions\SubscribeNewsletterSubscriberAction`: when an email matches a soft-deleted subscriber, restore and resubscribe that row, update its name, and preserve its token.
- Use `SoftDeletes` for user-managed records unless there is a clear reason not to.
- Back workflow state with a string enum (`NewsletterStatusEnum`) implementing Filament's `HasLabel`, `HasColor`, and `HasIcon`. Express lifecycle predicates as typed query scopes (`Newsletter::due()`, `NewsletterSubscriber::subscribed()` / `unsubscribed()`).

## Sending Pipeline Standards

Keep sending split across an orchestrating action, two queued jobs, and a mailable.

- Orchestrate through `Actions\SendNewsletterAction`: lock the newsletter row in a transaction, guard against stale or repeated sends, chunk subscribed recipients by `batch_chunk_size`, dispatch each `Jobs\SendNewsletterBatchJob` with `afterCommit()`, then mark the newsletter `sent` and stamp `sent_at` in that transaction.
- Keep `SendNewsletterBatchJob` and `SendNewsletterEmailJob` `ShouldBeUnique` with deterministic newsletter/chunk and newsletter/subscriber IDs. The cache locks prevent concurrent duplicate dispatches but are not the durable delivery record.
- `SendNewsletterBatchJob` fans a chunk out into per-recipient `SendNewsletterEmailJob`s. `SendNewsletterEmailJob` reloads the newsletter and subscriber, skips missing or unsubscribed recipients, and transactionally inserts and locks the unique `newsletter_deliveries` receipt. Skip completed receipts; after delivering `Mail\NewsletterMail` (view `vendra-newsletter::mail.newsletter`), stamp `sent_at`. Let transport exceptions roll back the receipt so retries remain possible.
- Configure jobs from `config/vendra-newsletter.php` via strict accessors: connection/queue from `queue.connection` / `queue.name`, `tries` from `queue.tries`, batch timeout from `queue.timeout`, and per-email timeout from `queue.email_timeout`.
- Keep both timeout defaults at 30 seconds and preserve `job timeout < Horizon supervisor timeout < queue retry_after` in host configuration. An empty newsletter connection means inherit the application's default queue connection.
- Rely on Spatie's tenant-aware queues to restore tenant context on the worker; never stamp or filter `tenant_id` manually in jobs or actions.
- Surface manual sending through the reusable `Filament/.../Newsletters/Actions/SendNewsletterTableAction`, wired into the table row actions and the edit page. Keep it confirmation-gated and hidden once the newsletter is `sent`.

## Scheduled Sending Standards

- `Console\Commands\SendScheduledNewslettersCommand` dispatches due newsletters (`Newsletter::due()`) wrapped in `TenantResolver::eachTenant()`. This is the tenant-agnostic way to run per tenant: the null resolver runs the callback once globally, the concrete provider loops every tenant. Never enumerate tenants or call `tenants:artisan` from this module.
- Register the schedule from `NewsletterServiceProvider::packageBooted()` with `callAfterResolving(Schedule::class, …)`, gated by config: return early when `schedule.enabled` is false and use `schedule.cron` for the cadence. Do not put the schedule in the host app's console routes.

## Unsubscribe Standards

- Keep the opt-out endpoint in `Http\Controllers\NewsletterUnsubscribeController` + `routes/web.php` (named `vendra-newsletter.unsubscribe`, loaded via `->hasRoute('web')`) + the `vendra-newsletter::unsubscribe` view.
- Resolve the subscriber by `unsubscribe_token` and set `unsubscribed_at` idempotently. The current tenant is resolved from the request domain, so keep tenant logic out of the controller.

## Filament Standards

Keep every resource that declares a `$cluster`, including its complete supporting tree, under `src/Filament/Clusters/Resources/` with the matching `Misaf\VendraNewsletter\Filament\Clusters\Resources` namespace and plugin discovery path. Resources without a cluster belong under `src/Filament/Resources/`.

- Register module UI through `NewsletterPlugin` and `NewsletterServiceProvider`; do not manually wire resources in unrelated panel providers.
- Keep resource classes thin. Delegate form schemas to `Schemas/*Form.php` and table configuration to `Tables/*Table.php`, and keep custom actions in `Actions/`.
- Use Filament v5 namespaces: form fields from `Filament\Forms\Components`, layout from `Filament\Schemas\Components`, table columns from `Filament\Tables\Columns`, filters from `Filament\Tables\Filters`, actions from `Filament\Actions`, notifications from `Filament\Notifications`, and icons from `Filament\Support\Icons\Heroicon`.
- Use translation keys from `vendra-newsletter::attributes`, `vendra-newsletter::navigation`, `vendra-newsletter::actions`, `vendra-newsletter::mail`, and `vendra-newsletter::newsletter_status_enum` for labels, breadcrumbs, navigation, action buttons, and email copy.

## Permissions And Navigation

Use policy enums and policies as the permission source.

- Add enum cases for every resource action the panel exposes.
- Authorize the custom send action explicitly with `Gate::allows('send', $record)`. Keep `NewsletterPolicy::send()` backed by `NewsletterPolicyEnum::SendNewsletterAction` (`send-newsletter`), rerun the package permission seeder after adding permissions, and deny updates after `sent`.
- Keep policy method names aligned with Filament actions: `viewAny`, `view`, `create`, `update`, `delete`, `deleteAny`, `restore`, `restoreAny`, `forceDelete`, `forceDeleteAny`, and `replicate` as applicable.
- Update `PermissionPolicySeeder` when new permissions are introduced.
- Keep the cluster in the `Marketing` navigation group. Keep its resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.

## Data And Localization

Migrations, factories, seeders, and translation files are part of the contract.

- Use the consolidated `create_newsletters_table.php` package migration stub, published into the host app on install. Create `newsletter_deliveries` after newsletters/subscribers and drop it first; keep its explicit unique `(newsletter_id, newsletter_subscriber_id)` receipt constraint.
- Use factories under `database/factories` and seeders under `database/seeders`. Keep them tenant-agnostic and let `BelongsToTenant` assign `tenant_id`.
- Keep demo fixtures deterministic and tenant-safe.
- Update all supported locales together and keep translation keys sorted.

## Testing And Verification

Prefer focused Pest tests in the module.

- Add or update tests for model contracts, policy permission coverage, resolver-derived tenant awareness, navigation/config/schedule behavior, translation parity, the send pipeline (batch fan-out, per-recipient delivery, durable replay suppression, and failed-send rollback), cross-tenant send isolation, soft-deleted subscriber restoration, and the unsubscribe flow.
- Fake the queue and mail (`Queue::fake()`, `Mail::fake()`) when asserting the send pipeline; use the vendra-testing `makeCurrentTestTenant()` helper in feature tests that need a real tenant context.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus `arch()->expect('Misaf\VendraNewsletter')->not->toUse('Misaf\VendraTenant')`.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-newsletter` and `composer stan`.
- If PHP files changed, run Pint for the touched code: `vendor/bin/pint --dirty --format agent`.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.
