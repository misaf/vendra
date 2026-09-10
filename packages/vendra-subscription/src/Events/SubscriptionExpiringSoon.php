<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * An active subscription is approaching its expiry and has not yet been
 * reminded. Consumers react by reminding the subscriber's owner. The engine
 * marks the subscription reminded regardless of whether a consumer acts.
 */
final readonly class SubscriptionExpiringSoon implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Subscription $subscription) {}
}
