<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Actions;

use Misaf\VendraOrder\Models\Order;

final class ConfirmOrderAction
{
    public function execute(Order $order): void
    {
        $order->confirm();
    }
}
