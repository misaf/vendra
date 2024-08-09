<?php

declare(strict_types=1);

namespace Termehsoft\Product\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Product\Models\ProductCategory;

final class ProductCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the ProductCategory "deleted" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function deleted(ProductCategory $productCategory): void
    {
        $this->deleteRelatedProducts($productCategory);

        $this->clearCaches($productCategory);
    }

    /**
     * Handle the ProductCategory "saved" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function saved(ProductCategory $productCategory): void
    {
        $this->clearCaches($productCategory);
    }

    /**
     * Clear relevant caches.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    private function clearCaches(ProductCategory $productCategory): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Delete related products when a product category is deleted or force deleted.
     *
     * @param  ProductCategory $productCategory
     * @return void
     */
    private function deleteRelatedProducts(ProductCategory $productCategory): void
    {
        $productCategory->products()->each(function ($product): void {
            $product->productPrices()->delete();
            $product->orderProducts()->delete();
            $product->delete();
        });
    }

    /**
     * Forget the product category row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('product-category-row-count');
    }
}
