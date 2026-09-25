<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Misaf\VendraSubscription\Models\Subscription;

final class SetSubscriptionAutoRenewAction
{
    public function execute(Subscription $subscription, bool $autoRenews): Subscription
    {
        $subscription->update(['auto_renews' => $autoRenews]);

        return $subscription;
    }
}
