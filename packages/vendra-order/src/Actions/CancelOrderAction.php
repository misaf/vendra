<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Misaf\VendraOrder\Models\Order;

final class CancelOrderAction
{
    /**
     * CancelOrderTransition locks the order and dispatches OrderCancelled.
     */
    public function execute(Order $order): void
    {
        $order->cancel();
    }
}
