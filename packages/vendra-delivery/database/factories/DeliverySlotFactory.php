<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<DeliverySlot>
 */
#[UseModel(DeliverySlot::class)]
final class DeliverySlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => 'Morning'],
            'starts_at' => '09:00:00',
            'ends_at' => '12:00:00',
            'capacity' => null,
            'active' => true,
        ];
    }

    public function window(string $name, string $startsAt, string $endsAt): static
    {
        return $this->state(fn (): array => [
            'name' => ['en' => $name],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (): array => ['capacity' => $capacity]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
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
