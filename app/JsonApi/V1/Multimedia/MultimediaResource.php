<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Multimedia;

use LaravelJsonApi\Core\Resources\JsonApiResource;

class MultimediaResource extends JsonApiResource
{
    public function attributes($request): iterable
    {
        return [
            'createdAt'   => $this->resource->created_at,
            'updatedAt'   => $this->resource->updated_at,
        ];
    }

    public function relationships($request): iterable
    {
        return [
            // @TODO
        ];
    }
}
