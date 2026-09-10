<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Enums\PayoutStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliatePayout;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<AffiliatePayout>
 */
#[UseModel(AffiliatePayout::class)]
final class AffiliatePayoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'amount' => fake()->numberBetween(1_000, 500_000),
            'status' => PayoutStatusEnum::Pending,
            'transaction_id' => null,
            'processed_at' => null,
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

    public function forAffiliate(Model|int $affiliate): static
    {
        return $this->state(fn (): array => [
            'affiliate_id' => $affiliate instanceof Model ? $affiliate->getKey() : $affiliate,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => PayoutStatusEnum::Completed,
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => PayoutStatusEnum::Failed]);
    }
}
