<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraSubscription\Actions\ApplySubscriptionPaymentResultAction;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionPaymentFailed;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Data\SubscriptionChargeResult;
use Misaf\VendraSupport\Enums\SubscriptionChargeStatus;

it('marks a payment paid and lets the engine raise the paid event', function (): void {
    $payment = SubscriptionPayment::factory()->create(['status' => SubscriptionPaymentStatus::Processing]);

    $result = app(ApplySubscriptionPaymentResultAction::class)->execute(
        $payment,
        new SubscriptionChargeResult(SubscriptionChargeStatus::Paid, providerReference: 'ref-1'),
    );

    expect($result->status)->toBe(SubscriptionPaymentStatus::Paid)
        ->and($result->paid_at)->not->toBeNull()
        ->and($result->provider_reference)->toBe('ref-1');
});

it('fails a pending-payment subscription and raises the failed event', function (): void {
    Event::fake([SubscriptionPaymentFailed::class, SubscriptionPaymentPaid::class]);
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::PendingPayment]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create(['status' => SubscriptionPaymentStatus::Processing]);

    $result = app(ApplySubscriptionPaymentResultAction::class)->execute(
        $payment,
        new SubscriptionChargeResult(SubscriptionChargeStatus::Failed, errorCode: 'declined', errorMessage: 'Card declined.'),
    );

    expect($result->status)->toBe(SubscriptionPaymentStatus::Failed)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Cancelled);
    Event::assertDispatched(SubscriptionPaymentFailed::class, fn (SubscriptionPaymentFailed $event): bool => $event->payment->is($result));
    Event::assertNotDispatched(SubscriptionPaymentPaid::class);
});

it('moves an active subscription to past due when its renewal payment fails', function (): void {
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Active]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create(['status' => SubscriptionPaymentStatus::Processing]);

    app(ApplySubscriptionPaymentResultAction::class)->execute(
        $payment,
        new SubscriptionChargeResult(SubscriptionChargeStatus::Failed, errorCode: 'declined'),
    );

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::PastDue);
});
