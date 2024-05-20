<?php

declare(strict_types=1);

namespace App\Observers\Product;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ProductCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the product category "deleted" event.
     *
     * @param  \App\Models\Product\ProductCategory $productCategory
     * @return void
     */
    public function deleted(\App\Models\Product\ProductCategory $productCategory): void
    {
        $this->deleteRelatedProducts($productCategory);
    }

    /**
     * Delete related products when a product category is deleted or force deleted.
     *
     * @param  \App\Models\Product\ProductCategory $productCategory
     * @return void
     */
    private function deleteRelatedProducts(\App\Models\Product\ProductCategory $productCategory): void
    {
        $productCategory->products()->each(function ($product): void {
            $product->productPrices()->delete();
            $product->orderProducts()->delete();
            $product->delete();
        });
    }
}
