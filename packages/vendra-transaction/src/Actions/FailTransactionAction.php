<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Misaf\VendraTransaction\Models\Transaction;

final class FailTransactionAction
{
    public function execute(Transaction $transaction): void
    {
        $transaction->fail();
    }
}
