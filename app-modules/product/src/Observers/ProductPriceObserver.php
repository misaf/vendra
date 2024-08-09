<?php

declare(strict_types=1);

namespace Termehsoft\Product\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Product\Models\ProductPrice;

final class ProductPriceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the ProductPrice "created" event.
     *
     * @param ProductPrice $productPrice
     */
    public function created(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "deleted" event.
     *
     * @param ProductPrice $productPrice
     */
    public function deleted(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "force deleted" event.
     *
     * @param ProductPrice $productPrice
     */
    public function forceDeleted(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "restored" event.
     *
     * @param ProductPrice $productPrice
     */
    public function restored(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "updated" event.
     *
     * @param ProductPrice $productPrice
     */
    public function updated(ProductPrice $productPrice): void {}
}
