<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;

/**
 * @extends Factory<CartItem>
 */
#[UseModel(CartItem::class)]
final class CartItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'sellable_type' => 'product',
            'sellable_id' => fake()->numberBetween(1, 1000),
            'quantity' => fake()->numberBetween(1, 10),
            'metadata' => null,
        ];
    }

    public function forCart(Cart $cart): static
    {
        return $this->state(fn (): array => [
            'cart_id' => $cart->id,
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
