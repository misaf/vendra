<?php

declare(strict_types=1);

namespace App\Observers\Transaction;

use App\Models\Transaction\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class TransactionObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Transaction "created" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction): void {}

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleted(Transaction $transaction): void {}

    /**
     * Handle the Transaction "force deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction): void {}

    /**
     * Handle the Transaction "restored" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function restored(Transaction $transaction): void {}

    /**
     * Handle the Transaction "updated" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function updated(Transaction $transaction): void {}
}
