<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Enums\PlanLimit;

/**
 * Dispatched when an add pushes a store's usage of a plan limit past a warning threshold.
 */
final readonly class StoreLimitApproached implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Store $store, public PlanLimit $limit, public int $percent) {}
}
