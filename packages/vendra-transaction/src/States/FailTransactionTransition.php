<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\States;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Events\TransactionFailed;
use Misaf\VendraTransaction\Models\Transaction;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Spatie\ModelStates\Transition;

final class FailTransactionTransition extends Transition
{
    public function __construct(private readonly Transaction $transaction) {}

    public function handle(): Transaction
    {
        DB::transaction(function (): void {
            // Re-read under a row lock, since the transition was validated against an in-memory status.
            $this->transaction->refreshForUpdate();

            throw_unless(
                $this->transaction->status->canTransitionTo(Failed::class),
                TransitionNotFound::make($this->transaction->status::class, Failed::class, Transaction::class),
            );

            $this->transaction->status = new Failed($this->transaction);
            $this->transaction->save();

            event(new TransactionFailed($this->transaction));
        });

        return $this->transaction;
    }
}
