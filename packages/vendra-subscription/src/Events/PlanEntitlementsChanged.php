<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraSubscription\Models\Plan;

/**
 * Dispatched when a plan's unit allowance, limits or features change, which can
 * leave subscribers already on it using more than it now allows.
 */
final readonly class PlanEntitlementsChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Plan $plan) {}
}
