<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\LaravelAuthifyLog\Enums\AuthifyLogActionEnum;
use Misaf\VendraAuthifyLog\Models\AuthifyLog;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraUser\Models\User;

/**
 * @extends Factory<AuthifyLog>
 */
#[UseModel(AuthifyLog::class)]
final class AuthifyLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(AuthifyLogActionEnum::cases()),
            'ip_address' => fake()->ipv4(),
            'ip_country' => fake()->countryCode(),
            'user_agent' => fake()->userAgent(),
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

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
        ]);
    }
}
