<?php

declare(strict_types=1);

namespace Termehsoft\Transaction\Policies;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Transaction\Models\Transaction;

final class TransactionObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Transaction "created" event.
     *
     * @param Transaction $transaction
     */
    public function created(Transaction $transaction): void {}

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param Transaction $transaction
     */
    public function deleted(Transaction $transaction): void {}

    /**
     * Handle the Transaction "force deleted" event.
     *
     * @param Transaction $transaction
     */
    public function forceDeleted(Transaction $transaction): void {}

    /**
     * Handle the Transaction "restored" event.
     *
     * @param Transaction $transaction
     */
    public function restored(Transaction $transaction): void {}

    /**
     * Handle the Transaction "updated" event.
     *
     * @param Transaction $transaction
     */
    public function updated(Transaction $transaction): void {}
}
