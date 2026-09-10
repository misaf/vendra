<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Cart>
 */
#[UseModel(Cart::class)]
final class CartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
        ];
    }

    public function forOwner(Model $owner): static
    {
        return $this->state(fn (): array => [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
        ]);
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
}
