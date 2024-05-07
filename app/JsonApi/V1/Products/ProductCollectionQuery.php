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
            'filter.id.*'                           => JsonApiRule::integer(),
            'filter.exclude'                        => 'array',
            'filter.exclude.*'                      => JsonApiRule::integer(),
            'filter.product-category'               => JsonApiRule::integer(),
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
            'filter.quantity'                       => JsonApiRule::integer(),
            'filter.gt-quantity'                    => JsonApiRule::integer(),
            'filter.gte-quantity'                   => JsonApiRule::integer(),
            'filter.lt-quantity'                    => JsonApiRule::integer(),
            'filter.lte-quantity'                   => JsonApiRule::integer(),
            'filter.stock-threshold'                => JsonApiRule::integer(),
            'filter.gt-stock-threshold'             => JsonApiRule::integer(),
            'filter.gte-stock-threshold'            => JsonApiRule::integer(),
            'filter.lt-stock-threshold'             => JsonApiRule::integer(),
            'filter.lte-stock-threshold'            => JsonApiRule::integer(),
            'filter.in_stock'                       => JsonApiRule::boolean(),
            'filter.with-latest-product-price'      => 'array',
            'filter.with-latest-product-price.*'    => 'string',
            'filter.without-latest-product-price'   => 'array',
            'filter.without-latest-product-price.*' => 'string',
            'filter.has-multimedia'                 => JsonApiRule::boolean(),
            'filter.with-multimedia'                => 'array',
            'filter.with-multimedia.*'              => 'string',
            'filter.without-multimedia'             => 'array',
            'filter.without-multimedia.*'           => 'string',
            'filter.with-productCategory'           => 'array',
            'filter.with-productCategory.*'         => 'string',
            'filter.without-productCategory'        => 'array',
            'filter.without-productCategory.*'      => 'string',
            'filter.in-productCategory.*'           => JsonApiRule::integer(),
            'filter.not-in-productCategory.*'       => JsonApiRule::integer(),
            'filter.has-product-prices'             => JsonApiRule::boolean(),
            'filter.with-product-prices'            => 'array',
            'filter.with-product-prices.*'          => 'string',
            'filter.without-product-prices'         => 'array',
            'filter.without-product-prices.*'       => 'string',
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
            'page.number' => [JsonApiRule::integer(), 'min:1'],
            'page.size'   => [JsonApiRule::integer(), 'between:1,100'],
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
