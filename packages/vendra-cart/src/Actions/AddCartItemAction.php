<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;

final class AddCartItemAction
{
    /**
     * Add a sellable to the cart, merging quantities when it is already present.
     *
     * Merging reads the current quantity and writes back a sum, so two adds of
     * the same sellable racing each other would both read the same starting
     * value and the second would overwrite the first. The row lock inside the
     * transaction serializes them, and makes the "does a row already exist"
     * check hold until the write lands rather than letting both callers insert.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function execute(Cart $cart, Model $sellable, int $quantity = 1, ?array $metadata = null): CartItem
    {
        return DB::transaction(function () use ($cart, $sellable, $quantity, $metadata): CartItem {
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
