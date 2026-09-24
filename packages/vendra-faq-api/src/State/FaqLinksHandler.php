<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Misaf\VendraFaq\Models\Faq;

/**
 * @implements LinksHandlerInterface<Faq>
 */
final class FaqLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<Faq>  $builder
     * @return Builder<Faq>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            // The mapper only needs the category's id and name.
            ->with(['faqCategory:id,name', 'multimedia'])
            ->whereHas('faqCategory', fn (Builder $query): Builder => $query->active())
            ->active();

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
