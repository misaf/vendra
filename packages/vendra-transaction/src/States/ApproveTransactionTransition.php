<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\States;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Actions\SettleTransactionAction;
use Misaf\VendraTransaction\Events\TransactionApproved;
use Misaf\VendraTransaction\Models\Transaction;
use Spatie\ModelStates\Transition;

/**
 * Approval settles the transaction into the ledger atomically with the
 * state change: entries, cached balances, and the status column commit
 * together or not at all.
 */
final class ApproveTransactionTransition extends Transition
{
    public function __construct(private readonly Transaction $transaction) {}

    public function handle(): Transaction
    {
        DB::transaction(function (): void {
            resolve(SettleTransactionAction::class)->execute($this->transaction);

            $this->transaction->status = new Approved($this->transaction);
            $this->transaction->save();
        });

        event(new TransactionApproved($this->transaction));

        return $this->transaction;
    }
}
