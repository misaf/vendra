<?php

declare(strict_types=1);

namespace Misaf\VendraCartApi\State;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraApi\State\ResourceMapper;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraCart\Models\CartItem;
use Misaf\VendraCartApi\ApiResource\CartLine;
use Misaf\VendraCartApi\ApiResource\CartResource;
use UnexpectedValueException;

final class CartMapper implements ResourceMapper
{
    public function map(Model $model): CartResource
    {
        if (! $model instanceof Cart) {
            throw new UnexpectedValueException('Expected a cart model.');
        }

        return new CartResource(
            id: $model->id,
            token: $model->token,
            expiresAt: $model->expires_at?->toAtomString(),
            lines: $model->items
                ->map(fn (CartItem $item): CartLine => new CartLine(
                    id: $item->id,
                    sellableType: $item->sellable_type,
                    sellableId: $item->sellable_id,
                    quantity: $item->quantity,
                    metadata: $item->metadata,
                ))
                ->all(),
            ownerType: $model->owner_type,
            ownerId: $model->owner_id,
        );
    }
}
