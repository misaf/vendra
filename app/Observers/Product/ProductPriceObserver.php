<?php

declare(strict_types=1);

namespace App\Observers\Product;

use App\Models\Product\ProductPrice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;

final class ProductPriceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the product price "created" event.
     *
     * @param  ProductPrice  $productPrice
     * @return void
     */
    public function created(ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "deleted" event.
     *
     * @param  ProductPrice  $productPrice
     * @return void
     */
    public function deleted(ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "force deleted" event.
     *
     * @param  ProductPrice  $productPrice
     * @return void
     */
    public function forceDeleted(ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Handle the product price "restored" event.
     *
     * @param  ProductPrice  $productPrice
     * @return void
     */
    public function restored(ProductPrice $productPrice): void
    {
        // Implement if needed
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return Carbon
     */
    public function retryUntil(): Carbon
    {
        return now()->addMinutes(5);
    }

    /**
     * Handle the product price "updated" event.
     *
     * @param  ProductPrice  $productPrice
     * @return void
     */
    public function updated(ProductPrice $productPrice): void
    {
        // Implement if needed
    }
}
