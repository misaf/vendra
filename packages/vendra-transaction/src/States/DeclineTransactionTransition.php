<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\States;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Events\TransactionDeclined;
use Misaf\VendraTransaction\Models\Transaction;
use Spatie\ModelStates\Exceptions\TransitionNotFound;
use Spatie\ModelStates\Transition;

final class DeclineTransactionTransition extends Transition
{
    public function __construct(private readonly Transaction $transaction) {}

    public function handle(): Transaction
    {
        DB::transaction(function (): void {
            // Re-read under a row lock, since the transition was validated against an in-memory status.
            $this->transaction->refreshForUpdate();

            throw_unless(
                $this->transaction->status->canTransitionTo(Declined::class),
                TransitionNotFound::make($this->transaction->status::class, Declined::class, Transaction::class),
            );

            $this->transaction->status = new Declined($this->transaction);
            $this->transaction->save();

            event(new TransactionDeclined($this->transaction));
        });

        return $this->transaction;
    }
}
