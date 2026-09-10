<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Delivery>
 */
#[UseModel(Delivery::class)]
final class DeliveryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'scheduled_for' => now()->addDay()->toDateString(),
            'latitude' => 35.7000,
            'longitude' => 51.4000,
            'distance_km' => 1.5,
            'currency_code' => Config::string('app.currency', 'USD'),
            'fee_amount' => 0,
            'requires_quote' => false,
            'recipient_name' => fake()->name(),
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (): array => ['order_id' => $order->getKey()]);
    }

    public function inZone(DeliveryZone $zone): static
    {
        return $this->state(fn (): array => [
            'delivery_zone_id' => $zone->getKey(),
            'currency_code' => $zone->currency_code,
        ]);
    }

    public function inSlot(DeliverySlot $slot): static
    {
        return $this->state(fn (): array => ['delivery_slot_id' => $slot->getKey()]);
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
