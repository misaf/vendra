<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionCancelled;
use Misaf\VendraSubscription\Models\Subscription;

final class CancelSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        $cancelled = false;

        $result = DB::transaction(function () use ($subscription, &$cancelled): Subscription {
            $lockedSubscription = $subscription->refreshForUpdate();

            throw_if($lockedSubscription->trashed(), (new ModelNotFoundException)->setModel(Subscription::class));

            if ($lockedSubscription->status === SubscriptionStatus::Cancelled) {
                return $lockedSubscription;
            }

            if (! $lockedSubscription->canBeCancelled()) {
                throw new LogicException("Subscription [{$lockedSubscription->id}] is {$lockedSubscription->status->value} and cannot be cancelled.");
            }

            $lockedSubscription->payments()
                ->open()
                ->lockForUpdate()
                ->get()
                ->each->cancel();

            $lockedSubscription->cancel();
            $cancelled = true;

            return $lockedSubscription;
        });

        if ($cancelled) {
            event(new SubscriptionCancelled($result));
        }

        return $result;
    }
}
