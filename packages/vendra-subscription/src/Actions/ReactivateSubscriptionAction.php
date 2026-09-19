<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Models\Subscription;

final readonly class ReactivateSubscriptionAction
{
    public function __construct(private SubscribeAction $subscribe) {}

    /**
     * The subscription is locked while its status is checked, so two
     * reactivations of the same subscription cannot both open a new period.
     */
    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription): Subscription {
            $lockedSubscription = $subscription->refreshForUpdate();

            throw_if($lockedSubscription->trashed(), (new ModelNotFoundException)->setModel(Subscription::class));

            if (! $lockedSubscription->canBeReactivated()) {
                throw new LogicException("Subscription [{$lockedSubscription->id}] is {$lockedSubscription->status->value} and cannot be reactivated.");
            }

            $subscriber = $lockedSubscription->subscriber()->firstOrFail();
            $plan = $lockedSubscription->plan()->firstOrFail();

            if (! $subscriber instanceof SubscriptionSubscriber) {
                throw new LogicException("Subscription [{$lockedSubscription->id}] has an unsupported subscriber.");
            }

            return $this->subscribe->execute($subscriber, $plan);
        });
    }
}
