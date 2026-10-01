<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Contracts;

interface StockRestorer
{
    public function sellableType(): ?string;

    /** @param array<int, int> $quantities */
    public function restore(array $quantities): void;
}
