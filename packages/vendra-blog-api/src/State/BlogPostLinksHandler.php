<?php

declare(strict_types=1);

namespace Misaf\VendraBlogApi\State;

use Illuminate\Support\Arr;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraBlog\Models\BlogPost;

/**
 * @implements LinksHandlerInterface<BlogPost>
 */
final class BlogPostLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<BlogPost>  $builder
     * @return Builder<BlogPost>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            // The mapper renders the category as a reference — its id and its
            // localized name — so selecting the rest of the row was dead weight
            // on every page of a collection response.
            ->with(['blogPostCategory:id,name', 'multimedia'])
            ->whereHas('blogPostCategory', fn (Builder $query): Builder => $query->where('active', true))
            ->where('active', true);

        if (! (Arr::get($context, 'operation', null)) instanceof CollectionOperationInterface) {
            $mcpData = Arr::get($context, 'mcp_data', []);
            $builder->whereKey(Arr::get($uriVariables, 'id', is_array($mcpData) ? (Arr::get($mcpData, 'id', null)) : null));
        }

        return $builder;
    }
}
