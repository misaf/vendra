<?php

declare(strict_types=1);

namespace Database\Factories\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderProduct;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
