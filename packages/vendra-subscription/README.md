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
- Emits lifecycle events (`SubscriptionPaymentPaid`/`Failed`, `SubscriptionActivated`, `SubscriptionCancelled`, `SubscriptionExpiringSoon`, `SubscriptionGraceExpired`) for host reactions

The engine is subscriber-agnostic: subscribe, activate, charge, and enforce all operate through the `SubscriptionSubscriber` contract and never reference a concrete subscriber. Subscriber-specific reactions — the concrete subscriber model, quota enforcement, provisioning, and contact notifications — belong to the host application, which implements the contract and subscribes to the engine's events. Provider adapters implement the `SubscriptionCharger` contract exposed by `misaf/vendra-support`; they must never collect more than once for the same idempotency key and financial payload.

## Requirements

- PHP 8.4+
- Laravel 13
- `misaf/vendra-support`

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

Requeue stale, interrupted, or reconciliation-ready payment operations after
an outage with:

```bash
php artisan vendra-subscription:recover-payments
```

Inspect reconciliation, stalled-processing, and paid-but-not-activated backlog
without mutating payments:

```bash
php artisan vendra-subscription:report-payment-backlog
php artisan vendra-subscription:report-payment-backlog --stale-minutes=60
```

## Testing

Run the package checks from the project root:

```bash
php artisan test --compact --testsuite=vendra-subscription
composer stan
```

## License

MIT. See [LICENSE](LICENSE).
