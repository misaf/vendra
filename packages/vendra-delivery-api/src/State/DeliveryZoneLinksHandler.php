<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

use Illuminate\Support\Arr;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraDelivery\Models\DeliveryZone;

/**
 * @implements LinksHandlerInterface<DeliveryZone>
 */
final class DeliveryZoneLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<DeliveryZone>  $builder
     * @return Builder<DeliveryZone>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder->where('active', true);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
