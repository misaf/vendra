<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraSubscription\Actions\CancelSubscriptionAction;
use Misaf\VendraSubscription\Actions\ExtendSubscriptionAction;
use Misaf\VendraSubscription\Actions\ReactivateSubscriptionAction;
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

it('allows each lifecycle change only from the statuses the console offers it for', function (SubscriptionStatus $status, bool $cancellable, bool $reactivatable, bool $extendable): void {
    $subscription = Subscription::factory()->make(['status' => $status, 'ends_at' => now()->addMonth()]);

    expect($subscription->canBeCancelled())->toBe($cancellable)
        ->and($subscription->canBeReactivated())->toBe($reactivatable)
        ->and($subscription->canBeExtended())->toBe($extendable);
})->with([
    'pending payment' => [SubscriptionStatus::PendingPayment, true, false, false],
    'active' => [SubscriptionStatus::Active, true, false, true],
    'past due' => [SubscriptionStatus::PastDue, true, true, false],
    'expired' => [SubscriptionStatus::Expired, false, true, false],
    'cancelled' => [SubscriptionStatus::Cancelled, false, true, false],
]);

it('does not extend an active subscription that never expires', function (): void {
    expect(Subscription::factory()->make(['status' => SubscriptionStatus::Active, 'ends_at' => null])->canBeExtended())->toBeFalse();
});

it('refuses to cancel an expired subscription', function (): void {
    Event::fake([SubscriptionCancelled::class]);
    $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::Expired]);

    expect(fn () => resolve(CancelSubscriptionAction::class)->execute($subscription))
        ->toThrow(LogicException::class)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Expired);
    Event::assertNotDispatched(SubscriptionCancelled::class);
});

it('refuses to reactivate a subscription that is active or awaiting payment', function (SubscriptionStatus $status): void {
    $subscription = Subscription::factory()->create(['status' => $status]);

    expect(fn () => resolve(ReactivateSubscriptionAction::class)->execute($subscription))
        ->toThrow(LogicException::class)
        ->and(Subscription::query()->count())->toBe(1);
})->with([
    'active' => [SubscriptionStatus::Active],
    'pending payment' => [SubscriptionStatus::PendingPayment],
]);
