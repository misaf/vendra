# Vendra Subscription

Generic plans and polymorphic subscriptions for Vendra applications.

## Features

- Defines plans with periods, pricing, trials, grace windows, feature entitlements, and usage limits
- Stores price and currency snapshots for each subscription period
- Persists provider-neutral subscription payment operations with stable idempotency keys and lifecycle states
- Supports any Eloquent subscriber through a polymorphic relationship and the `SubscriptionSubscriber` contract
- Enforces one active subscription per subscriber
- Provides pending-payment, active, past-due, lapsed, and expiry-reminder lifecycle primitives
- Runs a durable, retriable payment engine — queued collection (`ProcessSubscriptionPayment`), idempotent charge/retrieve, and reconciliation
- Emits lifecycle events (`SubscriptionPaymentPaid`/`Failed`, `SubscriptionActivated`, `SubscriptionCancelled`, `SubscriptionExpiringSoon`, `SubscriptionGraceExpired`, `SubscriptionInvoiceIssued`) for host reactions
- Adds the platform's tax to every charge and issues a numbered PDF invoice for each paid one

The engine is subscriber-agnostic: subscribe, activate, charge, and enforce all operate through the `SubscriptionSubscriber` contract and never reference a concrete subscriber. Subscriber-specific reactions — the concrete subscriber model, quota enforcement, provisioning, and contact notifications — belong to the host application, which implements the contract and subscribes to the engine's events. Suspending and reactivating a subscriber's units goes through the `SubscriptionUnitSuspender` contract; the package that owns the units binds it, and the default touches nothing. Provider adapters implement the `SubscriptionCharger` contract exposed by `misaf/vendra-support`; they must never collect more than once for the same idempotency key and financial payload.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-support`
- `mpdf/mpdf`, for invoice PDFs

## Installation

```bash
composer require misaf/vendra-subscription
php artisan vendor:publish --tag=vendra-subscription-migrations
php artisan migrate
```

The host application defines the inverse `morphMany` relationship and registers stable morph aliases for its subscriber models.

## Platform lifecycle actions

`CancelSubscriptionAction` cancels a subscription and its open payment
operations idempotently and emits `SubscriptionCancelled` for host reactions.
`ExtendSubscriptionAction` moves the end of an active,
expiring period later. `ReactivateSubscriptionAction` resolves the original
subscriber and plan, then creates a new period through `SubscribeAction`, so
payment handling and subscriber reactions are not duplicated. Plan changes and
renewals also continue to use `SubscribeAction`.

`ChangeSubscriptionPlanAction` applies an upgrade now and schedules anything
else for the next renewal in `scheduled_plan_id`. An upgrade from a paid,
running period keeps its end date and collects only the price difference for the
time left; `Support\PlanChangeQuote` computes that outcome for a panel to show
before the change. Amounts in different currencies are never netted, so moving
a paid period to a paid plan in another currency waits for the renewal too.
Each subscription, payment and invoice stores the currency it was created in,
so changing a plan's currency or the platform default never rewrites history. A downgrade the subscriber's current units exceed throws
`SubscriptionLimitException`, and so does any plan the bound `PlanUsageGuard`
refuses (the null default refuses nothing). `Support\PlanCoverage` holds both
checks: `assertCovers()` runs under the subscriber lock in the actions, and
`covers()` answers without locking so a plan picker can flag outgrown plans. Plans keep their per-unit caps in a
JSON `limits` map read with `Plan::limit()`, where a missing key means unlimited. Choosing the current plan drops a scheduled change.

`RenewSubscriptionAction` starts the next period on the scheduled plan, or the
same one, for a period that is no longer active. Within the grace window it
continues from the old end date, so paying late loses no paid time; afterwards it
starts now. A scheduled plan the subscriber has outgrown since choosing it
is dropped: the period renews on the current plan and `ScheduledPlanChangeDropped`
fires. `PlanCoverage::renewalPlan()` answers which plan a renewal will start, so
panels can warn about an outgrown scheduled plan ahead of time, and
`PlanCoverage::renewalBlocked()` whether the stores have outgrown that plan too,
in which case the renewal is refused and the subscriber has to change plan.
`UpdatePlanAction` fires `PlanEntitlementsChanged` when a plan's `max_units`,
`limits` or `features` change.
`Subscription::onPlanWithFeature()` scopes periods whose plan includes a feature. `vendra-subscription:enforce` renews every lapsed period whose
`auto_renews` flag is set before expiring it; `SetSubscriptionAutoRenewAction`
turns the flag on or off. Grace is measured from the last period that was ever
live (`activated_at`, the `activated()` scope), so an unpaid renewal never
postpones unit suspension. Schedule `vendra-subscription:enforce` (hourly) and
`vendra-subscription:recover-payments` in the host.

`Subscription::canBeCancelled()`, `canBeReactivated()`, and `canBeExtended()`
decide which change a status allows: cancel while pending payment, active, or
past due (`SubscriptionStatus::cancellable()`); reactivate while cancelled,
expired, or past due; extend while active with an end date. The payments a
cancellation stops are the `SubscriptionPayment::open()` scope. The actions refuse anything else and the console shows its
buttons from the same predicates. Reactivation locks the subscription while it
checks.

Payments an operator has to look at — awaiting customer action, needing
reconciliation, or with a failed refund — are the `SubscriptionPayment::needingReview()`
scope, which the console's Needs attention widget counts. `paidBetween($from, $until)`
selects the payments paid in a window, which the console's revenue totals sum.

Requeue stale, interrupted, or reconciliation-ready payment operations after
an outage with:

```bash
php artisan vendra-subscription:recover-payments
```

It requeues the `SubscriptionPayment::dueForRecovery()` scope: pending, processing
or unreconciled payments whose retry is due, and paid payments awaiting activation.

Inspect reconciliation, stalled-processing, and paid-but-not-activated backlog
without mutating payments:

```bash
php artisan vendra-subscription:report-payment-backlog
php artisan vendra-subscription:report-payment-backlog --stale-minutes=60
```

Its counts come from the `needsReconciliation()`, `stalledProcessing($threshold)`
and `awaitingActivation()` scopes.

## Tax and invoices

Plan prices are net. `SubscribeAction` adds the tax from the bound
`Contracts\BillingProfile` (`taxRate()` in basis points, so 1900 is 19%) and
stores the payment's `net_amount`, `tax_amount`, `tax_rate` and the collected
total in `amount`, using `Support\TaxedAmount` (rounded half up). The default
`Support\NullBillingProfile` adds no tax and names the app as seller; a host
binds its own profile to set the rate and the seller details. A panel that checks
a wallet or shows a charge before it happens adds the same tax with
`TaxedAmount::withProfileTax()`.

Every paid payment with an amount gets one `Models\SubscriptionInvoice`, issued
by the queued `IssueInvoiceOnPayment` listener through
`IssueSubscriptionInvoiceAction`. Issuing is idempotent per payment. Numbers are
gapless per year of payment (`INV-2026-000001`), taken from the locked
`subscription_invoice_sequences` row. The invoice snapshots the seller from the
profile, the buyer from `SubscriptionSubscriber::billingDetails()`, the amounts
and the plan line, so it never changes afterwards. `SubscriptionInvoiceIssued`
fires after commit for host reactions such as email.

`Support\SubscriptionInvoicePdf::render($invoice, $locale)` renders the PDF on
demand from that snapshot with mpdf, right to left for `fa`, `ar`, `he` and
`ur`. PDFs are not stored. Mark `IssueInvoiceOnPayment` as not tenant-aware in a
multitenant host, as it runs outside any tenant.

## Panel labels

`SubscriptionStatus` implements Filament's `HasLabel` and `HasColor`, and
`SubscriptionPaymentStatus` and `PeriodUnit` implement `HasLabel`, with translations in
`vendra-subscription::enums`. A `->badge()` column or entry that returns the
status is translated and colored without `formatStateUsing()`, and
`->options(PeriodUnit::class)` builds a translated select.

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-subscription
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
