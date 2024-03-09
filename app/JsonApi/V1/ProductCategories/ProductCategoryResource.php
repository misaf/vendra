<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductCategories;

use App\Traits\LocalizableAttributesTrait;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class ProductCategoryResource extends JsonApiResource
{
    use LocalizableAttributesTrait;

    public function attributes($request): iterable
    {
        $locale = $request->query('locale');

        return [
            'name'        => $this->getLocalizedAttribute('name', $locale) ?: null,
            'description' => $this->getLocalizedAttribute('description', $locale) ?: null,
            'slug'        => $this->getLocalizedAttribute('slug', $locale) ?: null,
            'position'    => $this->position,
            'status'      => $this->status,
            'createdAt'   => $this->resource->created_at,
            'updatedAt'   => $this->resource->updated_at,
        ];
    }

    public function relationships($request): iterable
    {
        return [
            $this->relation('multimedia'),
            $this->relation('products')
        ];
    }
}
