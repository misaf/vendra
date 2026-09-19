<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Actions;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraWishlist\Models\Wishlist;

final class RemoveWishlistItemAction
{
    /**
     * Removing a sellable that was never saved is not an error.
     */
    public function execute(Wishlist $wishlist, Model $sellable): bool
    {
        $removed = $wishlist->items()
            ->where('sellable_type', $sellable->getMorphClass())
            ->where('sellable_id', $sellable->getKey())
            ->delete();

        return $removed > 0;
    }
}
