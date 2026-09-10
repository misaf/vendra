<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraWishlist\Models\Wishlist;

/**
 * @extends Factory<Wishlist>
 */
#[UseModel(Wishlist::class)]
final class WishlistFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => (string) Str::uuid(),
            'name' => 'Favourites',
            'is_default' => true,
        ];
    }

    public function forOwner(Model $owner): static
    {
        return $this->state(fn (): array => [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
        ]);
    }

    public function named(string $name): static
    {
        return $this->state(fn (): array => [
            'name' => $name,
            'is_default' => false,
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
