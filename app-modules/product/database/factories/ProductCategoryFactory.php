<?php

declare(strict_types=1);

namespace Termehsoft\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Product\Models\ProductCategory;

final class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'slug'        => $this->faker->slug(),
            'status'      => $this->faker->boolean(),
        ];
    }
}
