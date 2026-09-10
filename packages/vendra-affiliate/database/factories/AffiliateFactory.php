<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraUser\Models\User;

/**
 * @extends Factory<Affiliate>
 */
#[UseModel(Affiliate::class)]
final class AffiliateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => mb_strtoupper(fake()->unique()->bothify('????####')),
            'commission_percent' => fake()->randomElement([5, 10, 15, 20, 30, 40, 50]),
            'signup_bounty' => null,
            'status' => fake()->randomElement(AffiliateStatusEnum::cases()),
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

    public function forUser(Model|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof Model ? $user->getKey() : $user,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => AffiliateStatusEnum::Active]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => AffiliateStatusEnum::Suspended]);
    }

    public function withSignupBounty(int $amount): static
    {
        return $this->state(fn (): array => ['signup_bounty' => $amount]);
    }
}
