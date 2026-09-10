<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraUser\Models\User;
use Misaf\VendraUserProfile\Models\UserProfile;

/**
 * @extends Factory<UserProfile>
 */
#[UseModel(UserProfile::class)]
final class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentences(1, true),
            'description' => fake()->realTextBetween(100, 200),
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'is_default' => fake()->boolean(1),
            'active' => fake()->boolean(80),
        ];
    }

    /**
     * No-op without a tenant provider, since there is no `tenant_id` column.
     */
    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function forUser(User|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof User ? $user->id : $user,
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
