<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Models\Transaction;

final class ApproveTransactionAction
{
    /**
     * The transaction is re-read under a row lock, so a stale copy cannot
     * overwrite a status another request has just changed.
     */
    public function execute(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction->refreshForUpdate()->approve();
        });
    }
}
