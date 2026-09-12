<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Misaf\VendraTransaction\Models\Transaction;

final class DeclineTransactionAction
{
    public function execute(Transaction $transaction): void
    {
        $transaction->decline();
    }
}
