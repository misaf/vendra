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
     * Handle the Product "created" event.
     *
     * @param Product $product
     * @return void
     */
    public function created(Product $product): void {}

    /**
     * Handle the Product "deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function deleted(Product $product): void
    {
        $this->deleteRelatedData($product);

        $this->forgetProductRowCountCache();
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function forceDeleted(Product $product): void {}

    /**
     * Handle the Product "restored" event.
     *
     * @param Product $product
     * @return void
     */
    public function restored(Product $product): void {}

    /**
     * Handle the Product "saved" event.
     *
     * @param Product $product
     * @return void
     */
    public function saved(Product $product): void
    {
        $this->forgetProductRowCountCache();
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param Product $product
     * @return void
     */
    public function updated(Product $product): void {}

    /**
     * Delete related data when a product is deleted or force deleted.
     *
     * @param Product $product
     * @return void
     */
    private function deleteRelatedData(Product $product): void
    {
        $product->productPrices()->delete();
        $product->orderProducts()->delete();
    }

    /**
     * Forget the product row count cache.
     * @return void
     */
    private function forgetProductRowCountCache(): void
    {
        Cache::forget('product_row_count');
    }
}
