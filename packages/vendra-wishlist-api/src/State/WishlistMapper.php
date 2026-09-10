<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraWishlist\Models\Wishlist;
use Misaf\VendraWishlist\Models\WishlistItem;
use Misaf\VendraWishlistApi\ApiResource\SavedItem;
use Misaf\VendraWishlistApi\ApiResource\WishlistResource;
use UnexpectedValueException;

final class WishlistMapper implements ResourceMapper
{
    public function map(Model $model): WishlistResource
    {
        if (! $model instanceof Wishlist) {
            throw new UnexpectedValueException('Expected a wishlist model.');
        }

        return new WishlistResource(
            id: $model->id,
            token: $model->token,
            name: $model->name,
            isDefault: $model->is_default,
            items: $model->items
                ->map(fn (WishlistItem $item): SavedItem => new SavedItem(
                    id: $item->id,
                    sellableType: $item->sellable_type,
                    sellableId: $item->sellable_id,
                    metadata: $item->metadata,
                    savedAt: $item->created_at->toAtomString(),
                ))
                ->all(),
            ownerType: $model->owner_type,
            ownerId: $model->owner_id,
        );
    }
}
