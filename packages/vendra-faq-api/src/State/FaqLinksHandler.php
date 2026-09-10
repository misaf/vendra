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
            // The mapper renders the category as a reference — its id and its
            // localized name — so selecting the rest of the row was dead weight
            // on every page of a collection response.
            ->with(['faqCategory:id,name', 'multimedia'])
            ->whereHas('faqCategory', fn (Builder $query): Builder => $query->where('active', true))
            ->where('active', true);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
