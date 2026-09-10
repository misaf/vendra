<?php

declare(strict_types=1);

namespace Misaf\VendraBlogApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Misaf\VendraBlog\Models\BlogPostCategory;

/**
 * @implements LinksHandlerInterface<BlogPostCategory>
 */
final class BlogPostCategoryLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<BlogPostCategory>  $builder
     * @return Builder<BlogPostCategory>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder
            ->with([
                'blogPosts' => function (Relation $relation): void {
                    $relation->getQuery()
                        ->select(['id', 'blog_post_category_id', 'name'])
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
