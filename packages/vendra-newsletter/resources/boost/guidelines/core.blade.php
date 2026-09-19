## Vendra Newsletter

The `misaf/vendra-newsletter` package owns newsletter domain behavior and the Filament admin UI for newsletters and subscribers, plus the queued send pipeline, the public unsubscribe flow, and a self-registered scheduled send.

### Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

### Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

- Register every table whose migration calls `TenantSchema::addTenantColumn()` with `TenantTableRegistry` in this package's service provider, preserving configured table names and connections, so `vendra-tenant:enable {tenant}` can retrofit schemas migrated before tenancy was enabled.

- Keep newsletter domain code inside `packages/vendra-newsletter` using the `Misaf\VendraNewsletter` namespace.
- Use this package for models, migrations, factories, seeders, policies, permission enums, Filament resources, mail, jobs, actions, the unsubscribe controller/route, console commands, translations, config, and package bootstrapping.
- Follow existing model conventions: tenant ownership, soft deletes, typed casts, the `NewsletterStatusEnum` lifecycle (`draft` → `scheduled` → `sent`), factories, and typed query scopes (`Newsletter::due()`, `NewsletterSubscriber::subscribed()` / `unsubscribed()`).
- Tenant awareness is owned by `misaf/vendra-support` via `Misaf\VendraSupport\Tenancy\TenantAwareness`, which derives purely from the bound `TenantResolver`. Installing a tenant provider (e.g. `misaf/vendra-tenant`) makes the app tenant-aware; without one the default null resolver keeps it disabled. The newsletter module defines no `tenant_aware` config.
- Keep the module tenant-agnostic: it must build and run with or without a tenant provider. Never reference a concrete provider such as `Misaf\VendraTenant`, `Tenant::`, or the `tenants:artisan` command anywhere — models, migrations, factories, seeders, fixtures, jobs, or commands. Let `BelongsToTenant` assign `tenant_id`; do not set it manually.
- Keep Filament resources thin by delegating forms to `Schemas/*Form.php` and tables to `Tables/*Table.php`. Keep the reusable Filament send button in `Filament/Clusters/Resources/Newsletters/Actions/SendNewsletterTableAction.php`; it delegates to the `Actions\SendNewsletterAction` domain action and is hidden once a newsletter is `sent`.
- Because the package resources declare a `$cluster`, keep their complete resource trees under `src/Filament/Clusters/Resources/`, use the matching `Misaf\VendraNewsletter\Filament\Clusters\Resources` namespace, and keep plugin discovery aligned. Any future resource without a cluster must instead live under `src/Filament/Resources/`.
- Keep cluster resources ungrouped and assign `$navigationSort` from their package-specific `NavigationPriority` cases; never hardcode numeric resource sort values.
- Provide separate singular and plural resource labels in `en`, `de`, and `fa`: model labels use the singular key, while navigation and plural model labels use the plural key. Keep navigation labels at 24 characters or fewer.
- Authorize the custom send action explicitly through `Gate::allows('send', $newsletter)`. Keep `NewsletterPolicy::send()` backed by the dedicated `send-newsletter` permission, seed new permissions through the package permission seeder, and prevent edits after a newsletter reaches `sent`.
- Keep subscriber email unique per tenant and `unsubscribe_token` globally unique. Creating an email that belongs to a soft-deleted subscriber must restore and resubscribe that row, update its name, and preserve its existing unsubscribe token through `Actions\SubscribeNewsletterSubscriberAction`.
- Keep the `newsletter_deliveries` receipt table in `create_newsletters_table.php`, created after newsletters and subscribers and dropped before them. Its unique `(newsletter_id, newsletter_subscriber_id)` constraint is the durable delivery-idempotency boundary.

### Sending pipeline

- Orchestrate sends through the `Actions\SendNewsletterAction` domain action. Lock the newsletter row in a database transaction, guard against stale or repeated sends, fan subscribed recipients out in chunks of `batch_chunk_size`, dispatch each `Jobs\SendNewsletterBatchJob` with `afterCommit()`, and mark the newsletter `sent` in the same transaction.
- Keep the queue layers split: `SendNewsletterBatchJob` (fan-out, `queue.timeout`) dispatches per-recipient `SendNewsletterEmailJob` (`queue.email_timeout`). Keep both jobs `ShouldBeUnique` with deterministic newsletter/recipient identifiers to suppress concurrent duplicate dispatches.
- In `SendNewsletterEmailJob`, reload tenant-scoped models, skip missing or unsubscribed recipients, then transactionally insert and lock the recipient's `newsletter_deliveries` receipt. Skip rows with `sent_at`; otherwise deliver `Mail\NewsletterMail` and stamp `sent_at`. A transport exception must roll back the receipt so the job remains retryable.
- Read all queue/batch settings (`batch_chunk_size`, `queue.connection`, `queue.name`, `queue.tries`, `queue.timeout`, `queue.email_timeout`) from `config/vendra-newsletter.php` through strict config accessors (`Config::integer`, `Config::string`). Rely on Spatie's tenant-aware queues to restore tenant context on the worker; do not stamp or query `tenant_id` manually.
- Keep the default batch and email timeouts at 30 seconds. When integrating with Horizon, preserve `job timeout < Horizon supervisor timeout < queue retry_after` (the host defaults are `30 < 60 < 90`) to avoid concurrent retries.

### Scheduled sending

- `Console\Commands\SendScheduledNewslettersCommand` dispatches due newsletters and wraps its query in `TenantResolver::eachTenant()` so each tenant's subscribers stay correctly scoped. The support contract owns the "run for every tenant" concern; never enumerate tenants here.
- The service provider self-registers the schedule via `callAfterResolving(Schedule::class, …)`, gated by config: skip when `schedule.enabled` is false, and use `schedule.cron` for the cadence. Scheduling lives in the package, not the host app.

### Unsubscribe

- Keep the public opt-out in `Http\Controllers\NewsletterUnsubscribeController` + `routes/web.php` (`vendra-newsletter.unsubscribe`) + `resources/views/unsubscribe.blade.php`. Resolve the subscriber by its `unsubscribe_token`; the current tenant is resolved from the request domain, so no tenant handling belongs in the controller. Opting out goes through `Actions\UnsubscribeNewsletterSubscriberAction`, which the admin subscription toggle also uses, paired with `ResubscribeNewsletterSubscriberAction`; an already unsubscribed row keeps its original `unsubscribed_at`.

### Conventions

- Follow Laravel comment style: document with PHPDoc (array shapes, generics, `@see`) and reserve inline comments for genuinely complex logic. Match the surrounding file and do not add comments that restate the code.
- Add or update Pest tests for policy coverage, config/navigation/schedule behavior, translation parity, model contracts, the send pipeline, durable delivery receipts and failed-send rollback, per-tenant isolation, subscriber restoration, and the unsubscribe flow.
- Keep tests purposeful and prevent unnecessary ones: cover behavior, contracts, and edge cases — not framework internals or trivially typed code.
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets plus a tenant-agnostic expectation, e.g. `arch()->expect('Misaf\VendraNewsletter')->not->toUse('Misaf\VendraTenant')`.

- Demo seeders declare their factory dependencies in `FACTORIES`; the shared base uses bundled fixtures when any declared factory is unavailable, including standalone Composer installs. Keep factories in development autoloading.
