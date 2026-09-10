<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Misaf\VendraFaq\Models\FaqCategory;

/**
 * @implements LinksHandlerInterface<FaqCategory>
 */
final class FaqCategoryLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<FaqCategory>  $builder
     * @return Builder<FaqCategory>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with([
                'faqs' => function (Relation $relation): void {
                    $relation->getQuery()
                        ->select(['id', 'faq_category_id', 'name'])
                        ->where('active', true);
                },
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
