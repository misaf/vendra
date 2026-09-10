<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionCancelled;
use Misaf\VendraSubscription\Models\Subscription;

final class CancelSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        $cancelled = false;

        $result = DB::transaction(function () use ($subscription, &$cancelled): Subscription {
            $lockedSubscription = Subscription::query()
                ->whereKey($subscription->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSubscription->status === SubscriptionStatus::Cancelled) {
                return $lockedSubscription;
            }

            $lockedSubscription->payments()
                ->whereIn('status', [
                    SubscriptionPaymentStatus::Pending->value,
                    SubscriptionPaymentStatus::Processing->value,
                    SubscriptionPaymentStatus::RequiresAction->value,
                    SubscriptionPaymentStatus::NeedsReconciliation->value,
                ])
                ->lockForUpdate()
                ->get()
                ->each->cancel();

            $lockedSubscription->cancel();
            $cancelled = true;

            return $lockedSubscription;
        });

        if ($cancelled) {
            SubscriptionCancelled::dispatch($result);
        }

        return $result;
    }
}
