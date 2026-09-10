<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Subscription;

final class ExtendSubscriptionAction
{
    public function execute(Subscription $subscription, Carbon $endsAt): Subscription
    {
        return DB::transaction(function () use ($subscription, $endsAt): Subscription {
            $lockedSubscription = Subscription::query()
                ->whereKey($subscription->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSubscription->status !== SubscriptionStatus::Active) {
                throw new LogicException("Subscription [{$lockedSubscription->id}] must be active before it can be extended.");
            }

            if ($lockedSubscription->ends_at === null) {
                throw new LogicException("Subscription [{$lockedSubscription->id}] does not expire.");
            }

            if ($endsAt->lessThanOrEqualTo($lockedSubscription->ends_at)) {
                throw new InvalidArgumentException('The new subscription end must be later than the current end.');
            }

            $lockedSubscription->forceFill([
                'ends_at' => $endsAt,
                'expiry_reminder_sent_at' => null,
            ])->save();

            return $lockedSubscription;
        });
    }
}
