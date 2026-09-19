<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraOrder\Events\OrderCancelled;
use Misaf\VendraOrder\Models\Order;

final class CancelOrderAction
{
    /**
     * The order is locked, so a concurrent cancellation fails its state
     * transition instead of returning the stock twice.
     */
    public function execute(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = $order->refreshForUpdate();

            $lockedOrder->cancel();

            event(new OrderCancelled($lockedOrder));
        });
    }
}
