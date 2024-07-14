<?php

declare(strict_types=1);

namespace Termehsoft\Order\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Order\Models\OrderProduct;

final class OrderProductObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the OrderProduct "created" event.
     *
     * @param OrderProduct $orderProduct
     */
    public function created(OrderProduct $orderProduct): void {}

    /**
     * Handle the OrderProduct "deleted" event.
     *
     * @param OrderProduct $orderProduct
     */
    public function deleted(OrderProduct $orderProduct): void {}

    /**
     * Handle the OrderProduct "force deleted" event.
     *
     * @param OrderProduct $orderProduct
     */
    public function forceDeleted(OrderProduct $orderProduct): void {}

    /**
     * Handle the OrderProduct "restored" event.
     *
     * @param OrderProduct $orderProduct
     */
    public function restored(OrderProduct $orderProduct): void {}

    /**
     * Handle the OrderProduct "updated" event.
     *
     * @param OrderProduct $orderProduct
     */
    public function updated(OrderProduct $orderProduct): void {}
}
