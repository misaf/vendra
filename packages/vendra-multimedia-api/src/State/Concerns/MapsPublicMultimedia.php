<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State\Concerns;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Misaf\VendraMultimediaApi\State\MultimediaResourceFactory;
use Misaf\VendraMultimediaApi\State\PublicMultimedia;

/**
 * The filter-map-values pipeline that turns a model's attached media into the
 * public subset, previously copied into every content mapper.
 *
 * It lives here rather than in vendra-api because vendra-multimedia-api already
 * depends on vendra-api; putting it the other way round would be a cycle.
 */
trait MapsPublicMultimedia
{
    /**
     * @param  bool  $onlyWhenLoaded  when true, an unloaded relation yields an
     *                                empty list instead of being lazy-loaded — the
     *                                category mappers rely on this to keep a
     *                                collection response from issuing a query per
     *                                row
     * @return list<MultimediaResource>
     */
    protected function publicMultimedia(Model $model, bool $onlyWhenLoaded = false, string $relation = 'multimedia'): array
    {
        if ($onlyWhenLoaded && ! $model->relationLoaded($relation)) {
            return [];
        }

        return $model->{$relation}
            ->filter(fn (Model $media): bool => PublicMultimedia::isPublic($media))
            ->map(fn (Model $media): MultimediaResource => MultimediaResourceFactory::make($media))
            ->values()
            ->all();
    }
}
