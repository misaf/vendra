<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData\Converter;

use App\Console\Commands\ConvertData\Interfaces\DataConverter;
use App\Console\Commands\ConvertData\Retriever\ProductDataRetriever;
use App\Models\Currency\Currency;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductPrice;
use Illuminate\Contracts\Filesystem\Filesystem as Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ProductDataConverter implements DataConverter
{
    public function __construct(public Storage $storage, public ProductDataRetriever $dataRetriever) {}

    public function migrate(): void
    {
        $this->resync();

        DB::transaction(function (): void {
            $this->migrateProductCategories();
            $this->migrateProductCategoryImages();
            $this->migrateProducts();
            $this->migrateProductImages();
            $this->migrateProductPrices();
        });
    }

    private function createNewProduct(mixed $oldProduct): Product
    {
        $getOldProductCategory = $this->dataRetriever->getOldProductCategoryById($oldProduct->product_category_id);

        $productCategoryId = ProductCategory::where('slug', $getOldProductCategory->slug)->value('id');

        $newProduct = new Product();

        $newProduct->setRawAttributes([
            'product_category_id' => $productCategoryId,
            'name'                => $oldProduct->name,
            'description'         => $oldProduct->description,
            'slug'                => $oldProduct->slug,
            'in_stock'            => 1,
            'available_soon'      => 0,
        ]);

        $newProduct->save();

        $newProduct->token = $oldProduct->token;

        $newProduct->save();

        return $newProduct;
    }

    private function createNewProductCategory(mixed $oldProductCategory): ProductCategory
    {
        $newProductCategory = new ProductCategory();

        $newProductCategory->setRawAttributes([
            'name'   => $oldProductCategory->name,
            'slug'   => $oldProductCategory->slug,
            'status' => 1
        ]);

        $newProductCategory->save();

        return $newProductCategory;
    }

    private function forceDeleteProductCategories(): void
    {
        ProductCategory::chunkById(100, function (Collection $productCategories): void {
            $productCategories->each(fn(ProductCategory $productCategory): bool => $productCategory->forceDelete());
        });
    }

    private function forceDeleteProducts(): void
    {
        Product::chunkById(100, function (Collection $products): void {
            $products->each(fn(Product $product): bool => $product->forceDelete());
        });
    }

    private function migrateProductCategories(): void
    {
        $oldProductCategories = $this->dataRetriever->getOldProductCategories();

        $oldProductCategories->each(fn($oldProductCategory): ProductCategory => $this->createNewProductCategory($oldProductCategory));
    }

    private function migrateProductCategoryImages(): void
    {
        $getOldProductCategories = $this->dataRetriever->getOldProductCategories();

        $getOldProductCategories
            ->reject(fn($oldBlogPostCategory): bool => $this->shouldSkipMigration($oldBlogPostCategory->image))
            ->each(function ($oldBlogPostCategory): void {
                ProductCategory::where('slug', $oldBlogPostCategory->slug)->first()
                    ->addMedia(storage_path('app/public/old-images/' . $oldBlogPostCategory->image))
                    ->preservingOriginal()
                    ->toMediaCollection();
            });
    }

    private function migrateProductImages(): void
    {
        $getOldProductImages = $this->dataRetriever->getOldProductImages();

        $getOldProductImages
            ->reject(fn($oldProductImage): bool => $this->shouldSkipMigration($oldProductImage->image))
            ->each(function ($oldProductImage): void {
                $getOldProduct = $this->dataRetriever->getOldProductById($oldProductImage->id);

                Product::where('slug', $getOldProduct->slug)->first()
                    ->addMedia(storage_path('app/public/old-images/' . $oldProductImage->image))
                    ->preservingOriginal()
                    ->toMediaCollection();
            });
    }

    private function migrateProductPrices(): void
    {
        $getOldProductPrices = $this->dataRetriever->getOldProductPrices();

        $getOldProductPrices
            ->reject(fn($oldProductPrice): bool => $oldProductPrice->price <= 0)
            ->each(function ($oldProductPrice): void {
                $getOldProduct = $this->dataRetriever->getOldProductById($oldProductPrice->product_id);
                $productId = Product::where('slug', $getOldProduct->slug)->value('id');

                ProductPrice::create([
                    'product_id'  => $productId,
                    'currency_id' => Currency::value('id'),
                    'price'       => $oldProductPrice->price
                ]);
            });
    }

    private function migrateProducts(): void
    {
        $oldProducts = $this->dataRetriever->getOldProducts();

        $oldProducts->each(fn($oldProduct): Product => $this->createNewProduct($oldProduct));
    }

    private function resync(): void
    {
        $this->forceDeleteProducts();
        $this->forceDeleteProductCategories();
        $this->truncateTables();
    }

    private function shouldSkipMigration(?string $image): bool
    {
        return (null === $image || ! $this->storage->exists('old-images/' . $image));
    }

    private function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        ProductPrice::truncate();
        Product::truncate();
        ProductCategory::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
