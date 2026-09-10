<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPageApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraCustomPage\Models\CustomPage;

/**
 * @implements LinksHandlerInterface<CustomPage>
 */
final class CustomPageLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<CustomPage>  $builder
     * @return Builder<CustomPage>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with(['customPageCategory:id,name', 'multimedia'])
            ->whereHas('customPageCategory', fn (Builder $query): Builder => $query->where('active', true))
            ->where('active', true);

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
