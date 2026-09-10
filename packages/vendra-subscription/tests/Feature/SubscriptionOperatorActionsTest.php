<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraSubscription\Actions\CancelSubscriptionAction;
use Misaf\VendraSubscription\Actions\ExtendSubscriptionAction;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionCancelled;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

it('cancels a subscription and its unfinished payment idempotently', function (): void {
    Event::fake([SubscriptionCancelled::class]);
    $subscription = Subscription::factory()->create([
        'status' => SubscriptionStatus::PendingPayment,
    ]);
    $payment = SubscriptionPayment::factory()->for($subscription)->create([
        'status' => SubscriptionPaymentStatus::Pending,
    ]);

    resolve(CancelSubscriptionAction::class)->execute($subscription);
    resolve(CancelSubscriptionAction::class)->execute($subscription->refresh());

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($payment->refresh()->status)->toBe(SubscriptionPaymentStatus::Cancelled);

    Event::assertDispatchedTimes(SubscriptionCancelled::class, 1);
});

it('extends only an active expiring subscription and clears its reminder', function (): void {
    $subscription = Subscription::factory()->create([
        'ends_at' => now()->addMonth(),
        'expiry_reminder_sent_at' => now(),
    ]);
    $newEnd = now()->addMonths(2)->startOfSecond();

    resolve(ExtendSubscriptionAction::class)->execute($subscription, $newEnd);

    expect($subscription->refresh()->ends_at?->equalTo($newEnd))->toBeTrue()
        ->and($subscription->expiry_reminder_sent_at)->toBeNull();
});
