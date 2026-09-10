<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;

/**
 * @extends Factory<WishlistItem>
 */
#[UseModel(WishlistItem::class)]
final class WishlistItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => Wishlist::factory(),
            'sellable_type' => 'product',
            'sellable_id' => fake()->numberBetween(1, 1000),
            'metadata' => null,
        ];
    }

    public function forWishlist(Wishlist $wishlist): static
    {
        return $this->state(fn (): array => [
            'wishlist_id' => $wishlist->id,
        ]);
    }

    public function forSellable(Model $sellable): static
    {
        return $this->state(fn (): array => [
            'sellable_type' => $sellable->getMorphClass(),
            'sellable_id' => $sellable->getKey(),
        ]);
    }
}
