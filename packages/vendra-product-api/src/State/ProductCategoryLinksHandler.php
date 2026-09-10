<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
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

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
