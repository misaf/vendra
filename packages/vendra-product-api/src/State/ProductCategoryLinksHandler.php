<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Misaf\VendraProduct\Models\ProductCategory;

/**
 * @implements LinksHandlerInterface<ProductCategory>
 */
final class ProductCategoryLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<ProductCategory>  $builder
     * @return Builder<ProductCategory>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with([
                'products:id,product_category_id,name',
                'multimedia',
            ])
            ->where('active', true);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
