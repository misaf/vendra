<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\State;

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

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
