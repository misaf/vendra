<?php

declare(strict_types=1);

namespace Database\Factories\Product;

use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'name'                => $this->faker->sentence(),
            'description'         => $this->faker->paragraph(),
            'slug'                => $this->faker->slug(),
            'quantity'            => $this->faker->numberBetween(1, 10),
            'in_stock'            => $this->faker->boolean(),
            'available_soon'      => $this->faker->boolean(),
            // 'availability_date'   => $this->faker->dateTimeAD(now()->days(30)),
        ];
    }
}
