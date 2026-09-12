---
name: vendra-subscription-development
description: "Create, modify, review, or test the Vendra Subscription module in packages/vendra-subscription — a generic, subscriber-agnostic subscription and durable-payment engine. Use for the Plan/Subscription/SubscriptionPayment models, PeriodUnit/SubscriptionStatus/SubscriptionPaymentStatus enums, the SubscriptionSubscriber contract, PlanInUseException/SubscriptionPaymentException/SubscriptionLimitException, the Charge/Apply/Activate/Subscribe/Cancel/Extend/Reactivate/Enforce actions, the ProcessSubscriptionPayment job, RecoverSubscriptionPaymentsCommand, ReportSubscriptionPaymentBacklogCommand, payment backlog observability, the SubscriptionPaymentPaid/Failed + SubscriptionActivated/Cancelled/ExpiringSoon/GraceExpired events, plan pricing/trials/entitlements, the default PlanSeeder, subscription scopes and lifecycle state, and subscription service-provider wiring. NOT for the concrete Reseller subscriber, StoreQuota, tenant provisioning, contact notifications, or the host reaction listeners — those live in the host app."
---

# Vendra Subscription

## Workflow

- Inspect `composer.json`, sibling files, and existing tests before changing the package.
- Use Laravel Boost `application-info` and `search-docs` before code changes.
- Apply `laravel-best-practices` to Laravel PHP and `pest-testing` whenever tests change.
- Apply `tailwindcss-development` only when changing Blade markup or Tailwind classes.
- Keep changes inside this package's boundary and preserve its public contracts.
- Add or update focused Pest coverage, then run `php artisan test --compact --testsuite=vendra-subscription` and `composer stan`.

## Translatable Persistence

- Making a persisted model field translatable is an explicit domain choice unless this package already requires it.
- Every field listed in a model's `$translatable` array must definitely use a JSON database column. Keep its model traits/casts, factories, validation, Filament locale UI, API serialization, and tests translation-aware.
- A field not listed in `$translatable` must use the appropriate scalar database type and must not use Spatie Translatable, translatable slug traits, locale switchers, translated callbacks, or translation-shaped array data.

## Vendra Transitive API Policy

- Treat a Vendra dependency intentionally exposed through the public API of a directly required Vendra platform package as part of the supported public contract of that package.
- Do not add a redundant direct Composer requirement solely because source code imports a type from that exposed dependency.
- Apply this only to Vendra platform packages listed under `require`; never extend it to `require-dev`, `suggest`, incidental implementation dependencies, or third-party packages. Removing or replacing an exposed dependency is a breaking change; keep `self.version` alignment across the Vendra package graph.

## Module Boundary

Treat `packages/vendra-subscription` as a **generic subscription + durable-payment engine**: plans, subscriptions, their lifecycle, and payment collection, with NO knowledge of the concrete subscriber. It operates through the `SubscriptionSubscriber` contract and raises domain events; the host implements the contract and reacts to the events.

- Use namespace `Misaf\VendraSubscription`. Own `Models\{Plan,Subscription,SubscriptionPayment}`, `Enums\{PeriodUnit,SubscriptionStatus,SubscriptionPaymentStatus}`, `Contracts\SubscriptionSubscriber`, `Exceptions\{PlanInUseException,SubscriptionPaymentException,SubscriptionLimitException}`, `Actions\{SubscribeAction,ActivateSubscriptionAction,CancelSubscriptionAction,ExtendSubscriptionAction,ReactivateSubscriptionAction,ChargeSubscriptionAction,ApplySubscriptionPaymentResultAction,EnforceSubscriptionsAction}`, `Jobs\ProcessSubscriptionPayment`, `Console\Commands\{EnforceSubscriptionsCommand,RecoverSubscriptionPaymentsCommand,ReportSubscriptionPaymentBacklogCommand}`, `Events\{SubscriptionPaymentPaid,SubscriptionPaymentFailed,SubscriptionActivated,SubscriptionCancelled,SubscriptionExpiringSoon,SubscriptionGraceExpired}`, `Listeners\ActivateSubscriptionOnPayment`, `Database\Seeders\PlanSeeder`, and `SubscriptionServiceProvider`. `PlanSeeder` ships the package's default Free/Basic/Pro catalog, seeded idempotently by slug; plans are edited from the console panel afterwards.
- Depend only on `misaf/vendra-support` (`ShouldLogActivity`, and the `SubscriptionCharger` contract the app implements) plus framework/Spatie packages. NEVER depend on `misaf/vendra-tenant`, `misaf/vendra-user`, `misaf/vendra-permission`, or `misaf/vendra-transaction`.
- **Subscriber decoupling:** `Subscription::subscriber()` is a generic `MorphTo` over `subscriber_type` / `subscriber_id`; never import a concrete subscriber here. `SubscriptionSubscriber` declares the subscription lookup, payer, notification, store-count, and suspend/reactivate operations the engine needs as domain methods, NOT the raw Eloquent relation — an Eloquent relation's declaring-model generic is invariant and cannot sit in an interface at phpstan level 10. The host subscriber model implements the contract and defines the inverse `morphMany`, registers stable morph aliases, and migrates pre-alias FQCN values. `SubscriptionFactory::forSubscriber($model)` stores the model's morph class, while a bare ID uses the explicit generic default type. The `(subscriber_type, active_subscriber_guard)` unique index enforces one active subscription per subscriber.
- **Event boundary:** the engine performs generic state changes and raises events; the host reacts. `ChargeSubscriptionAction` raises `SubscriptionPaymentPaid`; the package's `ActivateSubscriptionOnPayment` listener activates and raises `SubscriptionActivated`; `EnforceSubscriptionsAction` raises `SubscriptionExpiringSoon`/`SubscriptionGraceExpired`. Store reactivation stays ATOMIC inside activation/subscribe (via the contract, same transaction); only notifications and tenant suspension are event reactions. Register package listeners explicitly in `packageBooted()`; host `app/Listeners` are auto-discovered (Laravel 11+) — never also `Event::listen` them or reactions double-fire.
- Keep `ReportSubscriptionPaymentBacklogCommand` observational: count reconciliation, stale-processing (using `--stale-minutes`), and paid-but-not-activated payments, emit a warning when the total is non-zero, and never mutate payments. Recovery remains the responsibility of `RecoverSubscriptionPaymentsCommand`.
- The concrete subscriber (`App\Models\Reseller`, which implements `SubscriptionSubscriber`), quota (`StoreQuota`), provisioning (`Create/ProvisionTenantAction`), contact notifications, the host reaction listeners (`NotifyActivatedSubscriber`, `RemindExpiringSubscriber`, `SuspendSubscriberStores`), and the concrete `SubscriptionCharger` binding all live in the **host app** (`app/…`), not here. Console/reseller Filament panels are app-level (`app/Filament/{Console,Reseller}`).

## Domain Standards

- `Plan` owns `price`/`currency_code`, `trial_days`, `grace_days`, `max_units` (an opaque cap the app interprets), and a JSON `features` array (`Plan::allows()`). Deleting an in-use plan throws `PlanInUseException`.
- `Subscription` provides generic lifecycle only: scopes (`active`, `lapsed`, `expiringWithin`), `isActive()`/`isOnTrial()`/`suspendAt()`, price/currency snapshot, trial + reminder fields. Paid periods use `PendingPayment` until collection succeeds. It implements `ShouldLogActivity` (marker).
- Cancellation, extension, and reactivation use `CancelSubscriptionAction`, `ExtendSubscriptionAction`, and `ReactivateSubscriptionAction`. Cancellation emits `SubscriptionCancelled` after commit; reactivation, renewal, and plan changes converge on `SubscribeAction`. Keep subscriber-specific reactions outside this package.
- `SubscriptionPayment` is the durable, provider-neutral payment operation. Keep its provider, immutable idempotency key, provider reference, amount/currency snapshot, status, attempt count, sanitized failure details, and recovery timestamps auditable. A pending transaction is not a successful collection.
- `SubscriptionPaymentException` is the generic payment failure (missing currency, collection failed); the app throws it from its charge flow.
- Treat `SubscriptionCharge::reference` as an idempotency key. `SubscriptionCharger::charge()` and `retrieve()` return typed lifecycle results. Providers must resolve repeated identical operations to the original outcome, never collect twice, and reject reuse for different financial details. `ChargeSubscriptionAction` performs provider I/O only after commit and outside every database transaction or row lock.

## Migrations

- The root `database/migrations` baseline is the source of truth; each package migration `.stub` (`create_subscriptions_table`) must be byte-identical to its root counterpart (enforced by `FreshDatabaseSchemaTest`), which also forbids `add_/rename_/backfill_` schema follow-ups — fold new columns into the create migration on both sides. Host-only data migrations may normalize persisted morph types because the generic package cannot own concrete aliases. Cross-table references (`subscriber_id`, `plan_id`) use plain indexed columns, no DB foreign keys.

## Testing And Verification

- Keep tests purposeful: cover plan pricing/entitlements, `PlanInUseException`, subscription scopes/state transitions, and the payment-engine lifecycle (apply-result state machine, events raised) — not framework internals or the host's concrete-subscriber reactions (which the host app tests).
- Keep Pest architecture tests in `tests/ArchTest.php`: the `php`, `security`, and `laravel` presets, plus `not->toUse` for every domain provider including `Misaf\VendraTenant`, `Misaf\VendraUser`, and `Misaf\VendraPermission` — the engine must stay subscriber-agnostic.
- Run checks from the host app: `php artisan test --compact --testsuite=vendra-subscription` and `composer stan`.
- If PHP files changed, run `vendor/bin/pint --dirty --format agent`.
