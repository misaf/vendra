<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
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

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
