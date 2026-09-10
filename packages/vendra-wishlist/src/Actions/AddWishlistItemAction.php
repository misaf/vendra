<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;

final class AddWishlistItemAction
{
    /**
     * Save a sellable to a wishlist.
     *
     * Adding is idempotent: a customer tapping the heart twice keeps one row,
     * not two. The row lock inside the transaction makes the "is it already
     * saved" check hold until the write lands, so two taps racing each other
     * cannot both insert past the unique index.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function execute(Wishlist $wishlist, Model $sellable, ?array $metadata = null): WishlistItem
    {
        return DB::transaction(function () use ($wishlist, $sellable, $metadata): WishlistItem {
            $attributes = [
                'sellable_type' => $sellable->getMorphClass(),
                'sellable_id' => $sellable->getKey(),
            ];

            $item = $wishlist->items()->where($attributes)->lockForUpdate()->first();

            if (! $item instanceof WishlistItem) {
                $item = $wishlist->items()->make($attributes);
            }

            if ($metadata !== null) {
                $item->metadata = $metadata;
            }

            $item->save();

            return $item;
        }, attempts: 3);
    }
}
