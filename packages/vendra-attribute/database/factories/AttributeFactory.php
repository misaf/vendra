<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/** @extends Factory<Attribute> */
#[UseModel(Attribute::class)]
final class AttributeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->optional()->sentence(),
            'unit' => fake()->optional()->randomElement(['kg', 'cm', 'item', 'month']),
            'active' => true,
        ];
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

    public function active(): static
    {
        return $this->state(fn (): array => ['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
