<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<DeliveryZone>
 */
#[UseModel(DeliveryZone::class)]
final class DeliveryZoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => fake()->words(2, true)],
            'origin_latitude' => 35.6892,
            'origin_longitude' => 51.3890,
            'max_distance_km' => 12,
            'currency_code' => Config::string('app.currency', 'USD'),
            'fee_amount' => 0,
            'requires_quote' => false,
            'active' => true,
        ];
    }

    public function freeWithin(float $kilometres): static
    {
        return $this->state(fn (): array => [
            'max_distance_km' => $kilometres,
            'fee_amount' => 0,
            'requires_quote' => false,
        ]);
    }

    public function chargingWithin(float $kilometres, int $feeAmount): static
    {
        return $this->state(fn (): array => [
            'max_distance_km' => $kilometres,
            'fee_amount' => $feeAmount,
            'requires_quote' => false,
        ]);
    }

    public function quotedByHand(): static
    {
        return $this->state(fn (): array => [
            'max_distance_km' => null,
            'fee_amount' => 0,
            'requires_quote' => true,
        ]);
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
