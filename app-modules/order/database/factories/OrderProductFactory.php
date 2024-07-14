<?php

declare(strict_types=1);

namespace Termehsoft\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Order\Models\Order;
use Termehsoft\Order\Models\OrderProduct;
use Termehsoft\Product\Models\Product;

final class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'        => Order::factory(),
            'product_id'      => Product::factory(),
            'quantity'        => $this->faker->randomDigit(),
            'unit_price'      => $this->faker->numberBetween(100, 200),
            'tax_amount'      => $this->faker->numberBetween(10, 20),
            'discount_amount' => $this->faker->numberBetween(5, 10),
        ];
    }
}
