<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

/**
 * @extends Factory<Store>
 */
#[UseModel(Store::class)]
final class StoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(3),
            'description' => fake()->text(),
            'slug' => fn (array $attributes) => Str::slug(Arr::get($attributes, 'name')),
            'active' => fake()->boolean(),
            'locale' => null,
            'currency' => null,
            'timezone' => null,
            'metadata' => null,
            'billing_suspended_at' => null,
            'provisioning_status' => TenantProvisioningStatus::Ready,
            'provisioning_should_seed' => false,
            'provisioned_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['billing_suspended_at' => now()]);
    }

    /**
     * A store whose provisioning has not started yet.
     */
    public function provisioningPending(): static
    {
        return $this->state(fn (): array => ['provisioning_status' => TenantProvisioningStatus::Pending]);
    }

    /**
     * A store provisioning is currently working on.
     */
    public function provisioning(): static
    {
        return $this->state(fn (): array => ['provisioning_status' => TenantProvisioningStatus::Processing]);
    }

    /**
     * A store provisioning gave up on.
     */
    public function provisioningFailed(): static
    {
        return $this->state(fn (): array => ['provisioning_status' => TenantProvisioningStatus::Failed]);
    }
}
