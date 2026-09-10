<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<AffiliateCommission>
 */
#[UseModel(AffiliateCommission::class)]
final class AffiliateCommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'affiliate_referral_id' => null,
            'conversion_type' => fake()->randomElement(ConversionTypeEnum::cases()),
            'source_type' => null,
            'source_id' => null,
            'amount' => fake()->numberBetween(100, 100_000),
            'status' => CommissionStatusEnum::Pending,
            'affiliate_payout_id' => null,
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

    public function ofType(ConversionTypeEnum $conversionType): static
    {
        return $this->state(fn (): array => ['conversion_type' => $conversionType]);
    }

    public function fromSource(Model $source): static
    {
        return $this->state(fn (): array => [
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => CommissionStatusEnum::Pending]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => CommissionStatusEnum::Approved]);
    }

    public function paid(): static
    {
        return $this->state(fn (): array => ['status' => CommissionStatusEnum::Paid]);
    }

    public function reversed(): static
    {
        return $this->state(fn (): array => ['status' => CommissionStatusEnum::Reversed]);
    }
}
