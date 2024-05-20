<?php

declare(strict_types=1);

namespace App\Observers\Product;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ProductPriceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the product price "created" event.
     *
     * @param  \App\Models\Product\ProductPrice $productPrice
     * @return void
     */
    public function created(\App\Models\Product\ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "deleted" event.
     *
     * @param  \App\Models\Product\ProductPrice $productPrice
     * @return void
     */
    public function deleted(\App\Models\Product\ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "force deleted" event.
     *
     * @param  \App\Models\Product\ProductPrice $productPrice
     * @return void
     */
    public function forceDeleted(\App\Models\Product\ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "restored" event.
     *
     * @param  \App\Models\Product\ProductPrice $productPrice
     * @return void
     */
    public function restored(\App\Models\Product\ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "updated" event.
     *
     * @param  \App\Models\Product\ProductPrice $productPrice
     * @return void
     */
    public function updated(\App\Models\Product\ProductPrice $productPrice): void
    {
        // Implement if needed
    }
}
