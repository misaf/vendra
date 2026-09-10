<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateClick;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<AffiliateClick>
 */
#[UseModel(AffiliateClick::class)]
final class AffiliateClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referer' => fake()->optional()->url(),
            'landing_url' => fake()->url(),
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
}
