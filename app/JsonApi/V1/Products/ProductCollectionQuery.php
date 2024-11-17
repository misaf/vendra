<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Products;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

final class ProductCollectionQuery extends ResourceQuery
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'filter.id'                             => 'array',
            'filter.id.*'                           => 'integer',
            'filter.exclude'                        => 'array',
            'filter.exclude.*'                      => 'integer',
            'filter.product-category'               => 'integer',
            'filter.search'                         => 'string',
            'filter.slug'                           => 'string',
            'filter.in-slug'                        => 'array',
            'filter.in-slug.*'                      => 'string',
            'filter.not-in-slug'                    => 'array',
            'filter.not-in-slug.*'                  => 'string',
            'filter.token'                          => 'string',
            'filter.in-token'                       => 'array',
            'filter.in-token.*'                     => 'string',
            'filter.not-in-token'                   => 'array',
            'filter.not-in-token.*'                 => 'string',
            'filter.quantity'                       => 'integer',
            'filter.gt-quantity'                    => 'integer',
            'filter.gte-quantity'                   => 'integer',
            'filter.lt-quantity'                    => 'integer',
            'filter.lte-quantity'                   => 'integer',
            'filter.stock-threshold'                => 'integer',
            'filter.gt-stock-threshold'             => 'integer',
            'filter.gte-stock-threshold'            => 'integer',
            'filter.lt-stock-threshold'             => 'integer',
            'filter.lte-stock-threshold'            => 'integer',
            'filter.in_stock'                       => 'boolean',
            'filter.available-soon'                 => 'boolean',
            'filter.availability-date'              => JsonApiRule::dateTime(),
            'filter.with-latest-product-price'      => 'array',
            'filter.with-latest-product-price.*'    => 'string',
            'filter.without-latest-product-price'   => 'array',
            'filter.without-latest-product-price.*' => 'string',
            'filter.has-multimedia'                 => 'boolean',
            'filter.with-multimedia'                => 'array',
            'filter.with-multimedia.*'              => 'string',
            'filter.without-multimedia'             => 'array',
            'filter.without-multimedia.*'           => 'string',
            'filter.with-product-category'          => 'array',
            'filter.with-product-category.*'        => 'string',
            'filter.without-product-category'       => 'array',
            'filter.without-product-category.*'     => 'string',
            'filter.with-in-product-category'       => 'array',
            'filter.with-in-product-category.slug'  => 'string',
            'filter.with-in-product-category.*'     => 'string',
            'filter.in-product-category.*'          => 'integer',
            'filter.not-in-product-category.*'      => 'integer',
            'filter.has-product-prices'             => 'boolean',
            'filter.with-product-prices'            => 'array',
            'filter.with-product-prices.*'          => 'string',
            'filter.without-product-prices'         => 'array',
            'filter.without-product-prices.*'       => 'string',
            'filter.with-trashed'                   => 'boolean',
            'filter.only-trashed'                   => 'boolean',
            'include'                               => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'page.number' => ['integer', 'min:1'],
            'page.size'   => ['integer', 'between:1,100'],
            'sort'        => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
        ];
    }
}
