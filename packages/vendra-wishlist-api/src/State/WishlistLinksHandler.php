<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Misaf\VendraWishlist\Models\Wishlist;

/**
 * @implements LinksHandlerInterface<Wishlist>
 */
final class WishlistLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<Wishlist>  $builder
     * @return Builder<Wishlist>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $user = Auth::user();

        if (! $user instanceof Authenticatable) {
            return $builder->whereRaw('1 = 0');
        }

        $builder
            ->with('items')
            ->where('owner_type', $user->getMorphClass())
            ->where('owner_id', $user->getAuthIdentifier());

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
