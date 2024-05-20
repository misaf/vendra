<?php

declare(strict_types=1);

namespace App\Observers\Transaction;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class TransactionObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Transaction\Transaction $transaction): void {}

    public function deleted(\App\Models\Transaction\Transaction $transaction): void {}

    public function forceDeleted(\App\Models\Transaction\Transaction $transaction): void {}

    public function restored(\App\Models\Transaction\Transaction $transaction): void {}

    public function updated(\App\Models\Transaction\Transaction $transaction): void {}
}
