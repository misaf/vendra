<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Misaf\VendraOrder\Models\Order;

/**
 * Dispatched inside the cancellation's transaction rather than after commit,
 * so listeners that return stock commit or roll back with the cancellation.
 */
final readonly class OrderCancelled
{
    use Dispatchable;

    public function __construct(public Order $order) {}
}
