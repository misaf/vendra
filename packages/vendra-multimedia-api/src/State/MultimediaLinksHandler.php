<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @implements LinksHandlerInterface<PublicMultimedia>
 */
final class MultimediaLinksHandler implements LinksHandlerInterface
{
    /**
     * @param  Builder<PublicMultimedia>  $builder
     * @return Builder<PublicMultimedia>
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        $builder->select([
            'id', 'uuid', 'name', 'file_name', 'collection_name', 'mime_type', 'size',
            'disk', 'conversions_disk', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images',
        ]);

        PublicMultimedia::scope($builder);

        if (! ($context['operation'] ?? null) instanceof CollectionOperationInterface) {
            $mcpData = $context['mcp_data'] ?? [];
            $builder->whereKey($uriVariables['id'] ?? (is_array($mcpData) ? ($mcpData['id'] ?? null) : null));
        }

        return $builder;
    }
}
