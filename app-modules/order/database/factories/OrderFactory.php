<?php

declare(strict_types=1);

namespace Termehsoft\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\Currency;
use Termehsoft\Order\Enums\OrderStatusEnum;
use Termehsoft\Order\Models\Order;
use Termehsoft\Order\Services\OrderService;
use Termehsoft\User\Models\User;

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
