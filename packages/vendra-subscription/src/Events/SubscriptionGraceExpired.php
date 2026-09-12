<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * A subscriber's most recent subscription ended more than the plan's grace
 * window ago while it still holds active units. Consumers react by
 * suspending those units and notifying the contact. Carries the lapsed
 * subscription; resolve its subscriber to act.
 */
final readonly class SubscriptionGraceExpired implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Subscription $subscription) {}
}
