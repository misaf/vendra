<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraProduct\Models\ProductPrice;

/**
 * @implements LinksHandlerInterface<ProductPrice>
 */
final class ProductPriceLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<ProductPrice>  $builder
     * @return Builder<ProductPrice>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with('product:id,name')
            ->whereHas(
                'product.productCategory',
                fn (Builder $query): Builder => $query->where('active', true),
            );

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
