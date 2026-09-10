<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\State;

use Illuminate\Support\Arr;
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

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
