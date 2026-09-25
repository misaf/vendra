<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\Collection;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionExpiringSoon;
use Misaf\VendraSubscription\Events\SubscriptionGraceExpired;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * Notifications and unit suspension happen in the events' listeners.
 */
final readonly class EnforceSubscriptionsAction
{
    private const int EXPIRY_REMINDER_DAYS = 7;

    public function __construct(private RenewSubscriptionAction $renewSubscriptionAction) {}

    /**
     * @return array{renewed: int, expired: int, reminded: int, grace_expired: int}
     */
    public function execute(): array
    {
        [$renewed, $expired] = $this->renewOrExpireLapsedSubscriptions();

        return [
            'renewed' => $renewed,
            'expired' => $expired,
            'reminded' => $this->remindExpiringSubscriptions(),
            'grace_expired' => $this->flagPastGraceSubscribers(),
        ];
    }

    /**
     * A renewal that still has to be paid leaves the lapsed period to expire;
     * a failed payment then falls through to the grace window like any other.
     *
     * @return array{int, int}
     */
    private function renewOrExpireLapsedSubscriptions(): array
    {
        $renewed = 0;
        $expired = 0;

        Subscription::query()
            ->lapsed()
            ->with('subscriber')
            ->chunkById(100, function (Collection $subscriptions) use (&$renewed, &$expired): void {
                /** @var Collection<int, Subscription> $subscriptions */
                foreach ($subscriptions as $subscription) {
                    if ($this->renew($subscription)) {
                        $renewed++;
                    }

                    if ($subscription->refresh()->status === SubscriptionStatus::Active) {
                        $subscription->expire();
                        $expired++;
                    }
                }
            });

        return [$renewed, $expired];
    }

    private function renew(Subscription $subscription): bool
    {
        $subscriber = $subscription->subscriber;

        if (! $subscription->auto_renews || ! $subscriber instanceof SubscriptionSubscriber || ! $subscriber->canHoldUnits()) {
            return false;
        }

        try {
            $this->renewSubscriptionAction->execute($subscription);
        } catch (SubscriptionLimitException|SubscriptionPaymentException) {
            return false;
        }

        return true;
    }

    private function remindExpiringSubscriptions(): int
    {
        $reminded = 0;

        Subscription::query()
            ->expiringWithin(self::EXPIRY_REMINDER_DAYS)
            ->chunkById(100, function (Collection $subscriptions) use (&$reminded): void {
                /** @var Collection<int, Subscription> $subscriptions */
                foreach ($subscriptions as $subscription) {
                    $subscription->forceFill(['expiry_reminder_sent_at' => now()])->save();

                    event(new SubscriptionExpiringSoon($subscription));
                    $reminded++;
                }
            });

        return $reminded;
    }

    private function flagPastGraceSubscribers(): int
    {
        $flagged = 0;

        /** @var array<string, true> $processed */
        $processed = [];

        Subscription::query()
            ->whereNotIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PendingPayment->value,
            ])
            ->with('subscriber')
            ->chunkById(100, function (Collection $subscriptions) use (&$flagged, &$processed): void {
                /** @var Collection<int, Subscription> $subscriptions */
                foreach ($subscriptions as $subscription) {
                    $subscriber = $subscription->subscriber;

                    if (! $subscriber instanceof SubscriptionSubscriber) {
                        continue;
                    }

                    $key = $subscription->subscriber_type.':'.$subscription->subscriber_id;

                    if (isset($processed[$key])) {
                        continue;
                    }

                    $processed[$key] = true;

                    if ($subscriber->activeSubscribedUnitCount() === 0 || $subscriber->activeSubscription() !== null) {
                        continue;
                    }

                    $latest = self::latestActivatedSubscription($subscription);
                    $suspendAt = $latest?->suspendAt();

                    if ($latest === null || $suspendAt === null || $suspendAt->isFuture()) {
                        continue;
                    }

                    event(new SubscriptionGraceExpired($latest));
                    $flagged++;
                }
            });

        return $flagged;
    }

    /**
     * Grace runs from the last period that was ever live. An unpaid renewal
     * starts later than it, and measuring from it would postpone suspension
     * for as long as renewals keep failing.
     */
    private static function latestActivatedSubscription(Subscription $subscription): ?Subscription
    {
        return Subscription::query()
            ->where('subscriber_type', $subscription->subscriber_type)
            ->where('subscriber_id', $subscription->subscriber_id)
            ->activated()
            ->latest('starts_at')
            ->first();
    }
}
