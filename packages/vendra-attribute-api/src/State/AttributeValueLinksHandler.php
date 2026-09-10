<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Misaf\VendraAttribute\Models\AttributeValue;

/**
 * @implements LinksHandlerInterface<AttributeValue>
 */
final class AttributeValueLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<AttributeValue>  $builder
     * @return Builder<AttributeValue>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with('attribute:id,name')
            ->whereHas('attribute', fn (Builder $query): Builder => $query->where('active', true));

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
