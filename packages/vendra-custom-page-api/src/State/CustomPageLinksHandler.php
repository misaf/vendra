<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPageApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
