<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Misaf\VendraOrder\Models\Order;

final class CancelOrderAction
{
    public function execute(Order $order): void
    {
        $order->cancel();
    }
}
