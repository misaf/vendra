<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * Dispatched when a subscriber with active units is past its plan's grace window.
 */
final readonly class SubscriptionGraceExpired implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Subscription $subscription) {}
}
