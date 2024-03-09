<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Products;

use App\Traits\LocalizableAttributesTrait;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class ProductResource extends JsonApiResource
{
    use LocalizableAttributesTrait;

    public function attributes($request): iterable
    {
        $locale = $request->query('locale');

        return [
            'name'              => $this->getLocalizedAttribute('name', $locale) ?: null,
            'description'       => $this->getLocalizedAttribute('description', $locale) ?: null,
            'slug'              => $this->getLocalizedAttribute('slug', $locale) ?: null,
            'token'             => $this->token,
            'quantity'          => $this->quantity,
            'stock_threshold'   => $this->stock_threshold,
            'in_stock'          => $this->in_stock,
            'position'          => $this->position,
            'available_soon'    => $this->available_soon,
            'availability_date' => $this->availability_date,
            'createdAt'         => $this->resource->created_at,
            'updatedAt'         => $this->resource->updated_at,
        ];
    }

    public function relationships($request): iterable
    {
        return [
            $this->relation('productCategory'),
            $this->relation('multimedia'),
        ];
    }
}
