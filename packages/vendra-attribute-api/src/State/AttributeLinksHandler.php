<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Misaf\VendraAttribute\Models\Attribute;

/**
 * @implements LinksHandlerInterface<Attribute>
 */
final class AttributeLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<Attribute>  $builder
     * @return Builder<Attribute>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with('values:id,attribute_id,value,position')
            ->where('active', true);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
