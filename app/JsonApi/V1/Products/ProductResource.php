<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Products;

use App\Traits\LocalizableAttributesTrait;
use LaravelJsonApi\Core\Resources\JsonApiResource;

final class ProductResource extends JsonApiResource
{
    use LocalizableAttributesTrait;

    /**
     * Get the resource's attributes.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function attributes($request): iterable
    {
        $locale = app()->getLocale();

        return [
            'name'               => $this->getLocalizedAttribute('name', $locale) ?: null,
            'description'        => $this->getLocalizedAttribute('description', $locale) ?: null,
            'slug'               => $this->getLocalizedAttribute('slug', $locale) ?: null,
            'token'              => $this->token,
            'quantity'           => $this->quantity,
            'stock_threshold'    => $this->stock_threshold,
            'in_stock'           => $this->in_stock,
            'position'           => $this->position,
            'available_soon'     => $this->available_soon,
            'availability_date'  => $this->availability_date,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }

    /**
     * Get the resource's relationships.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function relationships($request): iterable
    {
        return [
            $this->relation('latestProductPrice'),
            $this->relation('multimedia'),
            $this->relation('productCategory'),
            $this->relation('productPrices'),
        ];
    }
}
