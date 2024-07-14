<?php

declare(strict_types=1);

namespace Termehsoft\Order\Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Order\Services\OrderService as ServicesOrderService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\Currency;
use Termehsoft\Order\Models\Order;
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
            'reference_code'  => ServicesOrderService::generateReferenceCode(),
            'status'          => $this->faker->randomElement(OrderStatusEnum::cases()),
        ];
    }
}
