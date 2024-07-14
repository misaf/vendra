<?php

declare(strict_types=1);

namespace Termehsoft\Order\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Order\Models\Order;

final class OrderObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Order "created" event.
     *
     * @param Order $order
     */
    public function created(Order $order): void {}

    /**
     * Handle the Order "deleted" event.
     *
     * @param Order $order
     */
    public function deleted(Order $order): void {}

    /**
     * Handle the Order "force deleted" event.
     *
     * @param Order $order
     */
    public function forceDeleted(Order $order): void {}

    /**
     * Handle the Order "restored" event.
     *
     * @param Order $order
     */
    public function restored(Order $order): void {}

    /**
     * Handle the Order "updated" event.
     *
     * @param Order $order
     */
    public function updated(Order $order): void {}
}
