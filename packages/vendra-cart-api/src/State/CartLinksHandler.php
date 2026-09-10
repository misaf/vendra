<?php

declare(strict_types=1);

namespace Misaf\VendraCartApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Misaf\VendraCart\Models\Cart;

/**
 * @implements LinksHandlerInterface<Cart>
 */
final class CartLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<Cart>  $builder
     * @return Builder<Cart>
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

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
