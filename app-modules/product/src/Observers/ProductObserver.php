<?php

declare(strict_types=1);

namespace Termehsoft\Product\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Product\Models\Product;

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
    public function created(Product $product): void
    {
        $this->clearCaches($product);
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function deleted(Product $product): void
    {
        $this->deleteRelatedData($product);

        $this->clearCaches($product);
    }

    /**
     * Handle the Product "saved" event.
     *
     * @param Product $product
     * @return void
     */
    public function saved(Product $product): void
    {
        $this->clearCaches($product);
    }

    /**
     * Clear relevant caches.
     *
     * @param Product $product
     * @return void
     */
    private function clearCaches(Product $product): void
    {
        $this->forgetRowCountCache();
        $this->forgetShowCache($product);
    }

    /**
     * Decrement the product row count cache.
     * @return void
     */
    private function decrementRowCountCache(): void
    {
        Cache::decrement('product-row-count');
    }

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
    private function forgetRowCountCache(): void
    {
        Cache::forget('product-row-count');
    }

    /**
     * Forget the product show cache.
     * @return void
     */
    private function forgetShowCache(Product $product): void
    {
        Cache::forget('show-product-' . $product->token);
    }

    /**
     * Increment the product row count cache.
     * @return void
     */
    private function increamentRowCountCache(): void
    {
        Cache::increment('product-row-count');
    }
}
