<?php

declare(strict_types=1);

namespace App\Observers\Product;

use App\Models\Product\ProductCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class ProductCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the ProductCategory "created" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function created(ProductCategory $productCategory): void {}

    /**
     * Handle the ProductCategory "deleted" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function deleted(ProductCategory $productCategory): void
    {
        $this->deleteRelatedProducts($productCategory);
    }

    /**
     * Handle the ProductCategory "force deleted" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function forceDeleted(ProductCategory $productCategory): void {}

    /**
     * Handle the ProductCategory "restored" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function restored(ProductCategory $productCategory): void {}

    /**
     * Handle the ProductCategory "updated" event.
     *
     * @param ProductCategory $productCategory
     * @return void
     */
    public function updated(ProductCategory $productCategory): void {}

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
}
