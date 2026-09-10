<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\OrderState;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Order>
 */
#[UseModel(Order::class)]
final class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $itemsAmount = fake()->randomElement([3800, 4800, 5600, 7200, 12000]);

        return [
            'number' => Order::generateNumber(),
            'currency_code' => Config::string('app.currency', 'USD'),
            'items_amount' => $itemsAmount,
            'delivery_amount' => 0,
            'total_amount' => $itemsAmount,
            'placed_at' => now(),
        ];
    }

    public function forCustomer(Model $customer): static
    {
        return $this->state(fn (): array => [
            'customer_type' => $customer->getMorphClass(),
            'customer_id' => $customer->getKey(),
        ]);
    }

    /**
     * @param  class-string<OrderState>  $state
     */
    public function withStatus(string $state): static
    {
        return $this->state(fn (): array => [
            'status' => $state,
        ]);
    }

    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }
}
