<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProductApi\State\Concerns\MapsCatalogResources;

/**
 * @implements LinksHandlerInterface<Product>
 */
final class ProductLinksHandler implements LinksHandlerInterface
{
    use MapsCatalogResources;

    /**
     * @param  Builder<Product>  $builder
     * @return Builder<Product>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with([
                // Rendered as a reference (id + localized name) by the mapper.
                'productCategory:id,name',
                'productPrices:id,product_id,currency_code,price',
                'latestProductPrice:product_prices.id,product_prices.product_id,product_prices.currency_code,product_prices.price',
                'multimedia',
                ...$this->attributeRelations(),
            ])
            ->whereHas('productCategory', fn (Builder $query): Builder => $query->where('active', true));

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
