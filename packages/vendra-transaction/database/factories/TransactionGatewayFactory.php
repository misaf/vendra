<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Services\TransactionGatewayRegistry;

/** @extends Factory<TransactionGateway> */
#[UseModel(TransactionGateway::class)]
final class TransactionGatewayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional()->sentence(),
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

    public function internal(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Internal Transactions',
            'slug' => TransactionGatewayRegistry::INTERNAL_GATEWAY_SLUG,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
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
