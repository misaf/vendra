<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\State\Concerns;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Misaf\VendraMultimediaApi\State\MultimediaResourceFactory;
use Misaf\VendraMultimediaApi\State\PublicMultimedia;

trait MapsPublicMultimedia
{
    /**
     * With `$onlyWhenLoaded`, an unloaded relation returns an empty list instead of a query.
     *
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
