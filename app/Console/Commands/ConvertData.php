<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Currency\Currency;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ConvertData extends Command
{
    protected $description = 'Command description';

    protected $signature = 'app:convert-data';

    public function handle(): int
    {
        DB::transaction(function (): void {
            $this->migrateProductCategories();
        });

        return Command::SUCCESS;
    }

    private function createNewProduct($oldProduct, $newProductCategory): Product
    {
        $newProduct = new Product();

        $newProduct->setRawAttributes([
            'product_category_id' => $newProductCategory->id,
            'name'                => $oldProduct->name,
            'description'         => $oldProduct->description,
            'slug'                => $oldProduct->slug,
            'token'               => $oldProduct->token,
            'in_stock'            => 1,
            'available_soon'      => 1,
        ]);

        $newProduct->save();

        return $newProduct;
    }

    private function createNewProductCategory($oldProductCategory): ProductCategory
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

    private function migrateProductCategories(): void
    {
        DB::connection('mysql_old')
            ->table('product_categories')
            ->whereNull('deleted_at')
            ->distinct()
            ->oldest('id')
            ->each(function ($oldProductCategory): void {
                $newProductCategory = $this->createNewProductCategory($oldProductCategory);

                $this->migrateProducts($oldProductCategory, $newProductCategory);
            });
    }

    private function migrateProductImages($oldProduct, $newProduct): void
    {
        DB::connection('mysql_old')
            ->table('product_images')
            ->where('product_id', $oldProduct->id)
            ->whereNull('deleted_at')
            ->distinct()
            ->oldest('id')
            ->each(function ($productImage) use ($newProduct): void {
                if (Storage::exists('old-images/' . $productImage->image)) {
                    $newProduct->addMedia(storage_path('app/old-images/' . $productImage->image))
                        ->preservingOriginal()
                        ->withResponsiveImages()
                        ->toMediaCollection();
                }
            });
    }

    private function migrateProductPrices($oldProduct, $newProduct): void
    {
        DB::connection('mysql_old')
            ->table('product_prices')
            ->where('product_id', $oldProduct->id)
            ->whereNull('deleted_at')
            ->distinct()
            ->oldest('id')
            ->each(function ($productPrice) use ($newProduct): void {
                if ($productPrice->price > 0) {
                    $newProduct->productPrices()
                        ->create([
                            'product_id'  => $newProduct->id,
                            'currency_id' => Currency::value('id'),
                            'price'       => $productPrice->price / 10,
                        ]);

                    $newProduct->in_stock = 0;

                    $newProduct->save();
                }
            });
    }

    private function migrateProducts($oldProductCategory, $newProductCategory): void
    {
        DB::connection('mysql_old')
            ->table('products')
            ->where('product_category_id', $oldProductCategory->id)
            ->whereNull('deleted_at')
            ->distinct()
            ->oldest('id')
            ->each(function ($oldProduct) use ($newProductCategory): void {
                $newProduct = $this->createNewProduct($oldProduct, $newProductCategory);

                $this->migrateProductImages($oldProduct, $newProduct);
                $this->migrateProductPrices($oldProduct, $newProduct);
            });
    }
}
