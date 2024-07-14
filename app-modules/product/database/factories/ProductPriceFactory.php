<?php

declare(strict_types=1);

namespace Termehsoft\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\Currency;
use Termehsoft\Product\Models\Product;
use Termehsoft\Product\Models\ProductPrice;

final class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'  => Product::factory(),
            'currency_id' => Currency::factory(),
            'price'       => $this->faker->numberBetween(50, 100),
        ];
    }
}
