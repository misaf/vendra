<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\Models\OrderLine;

/**
 * @extends Factory<OrderLine>
 */
#[UseModel(OrderLine::class)]
final class OrderLineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 4);
        $unitAmount = fake()->randomElement([3800, 4800, 5600, 7200]);

        return [
            'order_id' => Order::factory(),
            'sellable_type' => 'product',
            'sellable_id' => fake()->numberBetween(1, 1000),
            'name' => ['en' => fake()->words(2, true)],
            'currency_code' => Config::string('app.currency', 'USD'),
            'quantity' => $quantity,
            'unit_amount' => $unitAmount,
            'line_amount' => $unitAmount * $quantity,
            'metadata' => null,
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (): array => [
            'order_id' => $order->id,
            'currency_code' => $order->currency_code,
        ]);
    }

    public function forSellable(Model $sellable): static
    {
        return $this->state(fn (): array => [
            'sellable_type' => $sellable->getMorphClass(),
            'sellable_id' => $sellable->getKey(),
        ]);
    }
}
