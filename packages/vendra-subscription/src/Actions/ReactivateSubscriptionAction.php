<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use LogicException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Subscription;

final readonly class ReactivateSubscriptionAction
{
    public function __construct(private SubscribeAction $subscribe) {}

    public function execute(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::Active) {
            throw new LogicException("Subscription [{$subscription->id}] is already active.");
        }

        $subscriber = $subscription->subscriber()->firstOrFail();
        $plan = $subscription->plan()->firstOrFail();

        if (! $subscriber instanceof SubscriptionSubscriber) {
            throw new LogicException("Subscription [{$subscription->id}] has an unsupported subscriber.");
        }

        return $this->subscribe->execute($subscriber, $plan);
    }
}
