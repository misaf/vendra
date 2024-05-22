<?php

declare(strict_types=1);

namespace App\Observers\Product;

use App\Models\Product\ProductPrice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ProductPriceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the ProductPrice "created" event.
     *
     * @param ProductPrice $productPrice
     * @return void
     */
    public function created(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "deleted" event.
     *
     * @param ProductPrice $productPrice
     * @return void
     */
    public function deleted(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "force deleted" event.
     *
     * @param ProductPrice $productPrice
     * @return void
     */
    public function forceDeleted(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "restored" event.
     *
     * @param ProductPrice $productPrice
     * @return void
     */
    public function restored(ProductPrice $productPrice): void {}

    /**
     * Handle the ProductPrice "updated" event.
     *
     * @param ProductPrice $productPrice
     * @return void
     */
    public function updated(ProductPrice $productPrice): void {}
}
