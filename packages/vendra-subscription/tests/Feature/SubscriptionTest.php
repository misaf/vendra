<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

it('scopes to subscriptions that are active right now', function (): void {
    $active = Subscription::factory()->create();
    $future = Subscription::factory()->create(['starts_at' => now()->addWeek()]);
    $ended = Subscription::factory()->create(['ends_at' => now()->subDay()]);
    $expired = Subscription::factory()->expired()->create();

    $ids = Subscription::query()->active()->pluck('id');

    expect($ids)->toContain($active->id)
        ->not->toContain($future->id)
        ->not->toContain($ended->id)
        ->not->toContain($expired->id);
});

it('scopes to active subscriptions whose period has lapsed', function (): void {
    $lapsed = Subscription::factory()->create(['ends_at' => now()->subDay()]);
    $current = Subscription::factory()->create();

    expect(Subscription::query()->lapsed()->pluck('id'))
        ->toContain($lapsed->id)->not->toContain($current->id);
});

it('scopes to un-reminded subscriptions expiring within a window', function (): void {
    $soon = Subscription::factory()->create(['ends_at' => now()->addDays(3)]);
    $reminded = Subscription::factory()->create(['ends_at' => now()->addDays(3), 'expiry_reminder_sent_at' => now()]);
    $later = Subscription::factory()->create(['ends_at' => now()->addDays(30)]);

    expect(Subscription::query()->expiringWithin(7)->pluck('id'))
        ->toContain($soon->id)->not->toContain($reminded->id)->not->toContain($later->id);
});

it('reports trial and active state', function (): void {
    expect(Subscription::factory()->create(['trial_ends_at' => now()->addWeek()])->isOnTrial())->toBeTrue()
        ->and(Subscription::factory()->create(['trial_ends_at' => now()->subDay()])->isOnTrial())->toBeFalse()
        ->and(Subscription::factory()->create()->isActive())->toBeTrue()
        ->and(Subscription::factory()->expired()->create()->isActive())->toBeFalse();
});

it('transitions subscriptions through explicit lifecycle methods', function (): void {
    $pending = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    $active = Subscription::factory()->create();
    $lapsed = Subscription::factory()->create();
    $cancelled = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);

    $pending->activate();
    $active->markPastDue();
    $lapsed->expire();
    $cancelled->cancel();

    expect($pending->status)->toBe(SubscriptionStatus::Active)
        ->and($active->status)->toBe(SubscriptionStatus::PastDue)
        ->and($lapsed->status)->toBe(SubscriptionStatus::Expired)
        ->and($cancelled->status)->toBe(SubscriptionStatus::Cancelled);
});

it('derives the suspend date from its plan grace window', function (): void {
    $plan = Plan::factory()->graceDays(5)->create();
    $subscription = Subscription::factory()->for($plan)->create(['ends_at' => now()->addDays(10)]);

    expect($subscription->suspendAt()->toDateString())
        ->toBe(now()->addDays(15)->toDateString());

    expect(Subscription::factory()->neverExpires()->create()->suspendAt())->toBeNull();
});

it('enforces one active subscription per subscriber', function (): void {
    Subscription::factory()->forSubscriber(42)->create();

    expect(fn (): Subscription => Subscription::factory()->forSubscriber(42)->create())
        ->toThrow(QueryException::class);
});

it('allows a second subscription for a subscriber once the first is inactive', function (): void {
    Subscription::factory()->forSubscriber(7)->create(['status' => SubscriptionStatus::Cancelled]);

    $replacement = Subscription::factory()->forSubscriber(7)->create();

    expect($replacement->isActive())->toBeTrue();
});

it('persists auditable payment lifecycle data for a subscription', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create();

    expect($payment->subscription->is($subscription))->toBeTrue()
        ->and($subscription->payments()->sole()->is($payment))->toBeTrue()
        ->and($payment->attempt_count)->toBe(0)
        ->and($payment->status->value)->toBe('pending')
        ->and($payment->idempotency_key)->toBeUuid();
});

it('records payment processing through explicit lifecycle methods', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create([
        'failure_code' => 'previous_failure',
        'failure_message' => 'Previous failure.',
    ]);

    $payment->beginProcessing();

    expect($payment->status)->toBe(SubscriptionPaymentStatus::Processing)
        ->and($payment->attempt_count)->toBe(1)
        ->and($payment->processing_at)->not->toBeNull()
        ->and($payment->failure_code)->toBeNull()
        ->and($payment->failure_message)->toBeNull();

    $payment->recordProviderResult(
        SubscriptionPaymentStatus::Paid,
        'provider-payment-1',
        null,
        null,
    );

    expect($payment->status)->toBe(SubscriptionPaymentStatus::Paid)
        ->and($payment->provider_reference)->toBe('provider-payment-1')
        ->and($payment->paid_at)->not->toBeNull();
});

it('enforces unique payment idempotency keys', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    $idempotencyKey = '8f98df2e-38c2-4b87-8baa-768b9c806f87';

    SubscriptionPayment::factory()->for($subscription)->create([
        'idempotency_key' => $idempotencyKey,
    ]);

    expect(fn (): SubscriptionPayment => SubscriptionPayment::factory()->for($subscription)->create([
        'idempotency_key' => $idempotencyKey,
    ]))->toThrow(QueryException::class);
});

it('enforces unique provider payment identities', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);

    SubscriptionPayment::factory()->for($subscription)->create([
        'provider' => 'gateway',
        'provider_reference' => 'provider-payment-1',
    ]);

    expect(fn (): SubscriptionPayment => SubscriptionPayment::factory()->for($subscription)->create([
        'provider' => 'gateway',
        'provider_reference' => 'provider-payment-1',
    ]))->toThrow(QueryException::class);
});

it('prevents hard deletion of a subscription with payment history', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    SubscriptionPayment::factory()->for($subscription)->create();

    expect(fn (): ?bool => $subscription->forceDelete())->toThrow(QueryException::class);
});
