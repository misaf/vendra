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
        $settled = DB::transaction(function (): bool {
            /*
             | Re-read under a row lock: the transition was validated against an
             | in-memory status, and two workers approving the same transaction
             | from stale copies would otherwise both post it to the ledger.
             */
            $this->transaction->refreshForUpdate();

            if (! $this->transaction->status->canTransitionTo(Approved::class)) {
                return false;
            }

            resolve(SettleTransactionAction::class)->execute($this->transaction);

            $this->transaction->status = new Approved($this->transaction);
            $this->transaction->save();

            return true;
        });

        if ($settled) {
            event(new TransactionApproved($this->transaction));
        }

        return $this->transaction;
    }
}
