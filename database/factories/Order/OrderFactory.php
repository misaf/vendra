<?php

declare(strict_types=1);

namespace Database\Factories\Order;

use App\Enums\OrderStatusEnum;
use App\Models\Currency\Currency;
use App\Models\Order\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'currency_id'     => Currency::class,
            'description'     => $this->faker->paragraph(),
            'discount_amount' => $this->faker->numberBetween(5, 10),
            'reference_code'  => OrderService::generateReferenceCode(),
            'status'          => $this->faker->randomElement(OrderStatusEnum::cases()),
        ];
    }
}
