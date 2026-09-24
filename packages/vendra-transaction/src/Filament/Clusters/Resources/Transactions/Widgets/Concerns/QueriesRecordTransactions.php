<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Widgets\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraTransaction\Models\Transaction;

/**
 * @property ?Model $record
 */
trait QueriesRecordTransactions
{
    /**
     * Get the transactions, narrowed to the viewed user's wallets on a user's page.
     *
     * @return Builder<Transaction>
     */
    private function transactions(): Builder
    {
        $transactions = Transaction::query();

        return $this->record instanceof Model ? $transactions->ownedBy($this->record) : $transactions;
    }
}
