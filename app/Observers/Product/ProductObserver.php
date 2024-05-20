<?php

declare(strict_types=1);

namespace App\Observers\Product;

use App\Models\Product\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

final class ProductObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the product "deleted" event.
     *
     * @param  \App\Models\Product\Product  $product
     * @return void
     */
    public function deleted(\App\Models\Product\Product $product): void
    {
        $this->deleteRelatedData($product);

        $this->forgetProductRowCountCache();
    }

    /**
     * Handle the product "saved" event.
     *
     * @param  \App\Models\Product\Product  $product
     * @return void
     */
    public function saved(\App\Models\Product\Product $product): void
    {
        $this->forgetProductRowCountCache();
    }

    /**
     * Delete related data when a product is deleted or force deleted.
     *
     * @param  \App\Models\Product\Product  $product
     * @return void
     */
    private function deleteRelatedData(\App\Models\Product\Product $product): void
    {
        $product->productPrices()->delete();
        $product->orderProducts()->delete();
    }

    /**
     * Forget the product row count cache.
     *
     * @return void
     */
    private function forgetProductRowCountCache(): void
    {
        Cache::forget('product_row_count');
    }
}
