<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraUser\Models\User;

/**
 * @extends Factory<AffiliateReferral>
 */
#[UseModel(AffiliateReferral::class)]
final class AffiliateReferralFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'user_id' => User::factory(),
            'affiliate_click_id' => null,
            'attributed_at' => now(),
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

    public function forUser(Model|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof Model ? $user->getKey() : $user,
        ]);
    }
}
