<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductPrices;

use LaravelJsonApi\Core\Resources\JsonApiResource;

final class ProductPriceResource extends JsonApiResource
{
    public function attributes($request): iterable
    {
        return [
            'price'     => $this->price,
            'createdAt' => $this->resource->created_at,
            'updatedAt' => $this->resource->updated_at,
        ];
    }

    public function relationships($request): iterable
    {
        return [
            $this->relation('product'),
            $this->relation('currency'),
        ];
    }
}
