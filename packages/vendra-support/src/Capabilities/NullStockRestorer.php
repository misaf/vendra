<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use LogicException;
use Misaf\VendraSupport\Contracts\StockRestorer;

final class NullStockRestorer implements StockRestorer
{
    public function sellableType(): ?string
    {
        return null;
    }

    public function restore(array $quantities): void
    {
        throw new LogicException('Install a stock provider before cancelling an order with deducted stock.');
    }
}
