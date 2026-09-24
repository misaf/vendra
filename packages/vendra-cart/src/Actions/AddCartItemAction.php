<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;

final class AddCartItemAction
{
    /**
     * A row lock serializes concurrent adds so neither overwrites the other.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function execute(Cart $cart, Model $sellable, int $quantity = 1, ?array $metadata = null): CartItem
    {
        throw_if($quantity < 1, InvalidArgumentException::class, 'Quantity must be positive.');

        return DB::transaction(function () use ($cart, $sellable, $quantity, $metadata): CartItem {
            $cart->refreshForUpdate();

            $attributes = [
                'sellable_type' => $sellable->getMorphClass(),
                'sellable_id' => $sellable->getKey(),
            ];

            $item = $cart->items()->where($attributes)->lockForUpdate()->first();

            if (! $item instanceof CartItem) {
                $item = $cart->items()->make($attributes);
            }

            $item->quantity = $item->exists ? $item->quantity + $quantity : $quantity;

            if ($metadata !== null) {
                $item->metadata = $metadata;
            }

            $item->save();

            return $item;
        }, attempts: 3);
    }
}
