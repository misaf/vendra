<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Contracts\SubscriptionUnitSuspender;

final class NullSubscriptionUnitSuspender implements SubscriptionUnitSuspender
{
    public function suspendActiveUnits(Model&SubscriptionSubscriber $subscriber): int
    {
        return 0;
    }

    public function reactivateSuspendedUnits(Model&SubscriptionSubscriber $subscriber): int
    {
        return 0;
    }
}
