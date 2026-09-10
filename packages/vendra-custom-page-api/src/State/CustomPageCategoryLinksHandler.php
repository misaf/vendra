<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPageApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Misaf\VendraCustomPage\Models\CustomPageCategory;

/**
 * @implements LinksHandlerInterface<CustomPageCategory>
 */
final class CustomPageCategoryLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<CustomPageCategory>  $builder
     * @return Builder<CustomPageCategory>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with([
                'customPages' => function (Relation $relation): void {
                    $relation->getQuery()
                        ->select(['id', 'custom_page_category_id', 'name'])
                        ->where('active', true);
                },
            ])
            ->where('active', true);

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
