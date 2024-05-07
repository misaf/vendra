<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductPrices;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

final class ProductPriceCollectionQuery extends ResourceQuery
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
            'filter.id'                 => 'array',
            'filter.id.*'               => JsonApiRule::integer(),
            'filter.exclude'            => 'array',
            'filter.exclude.*'          => JsonApiRule::integer(),
            'filter.product'            => JsonApiRule::integer(),
            'filter.currency'           => JsonApiRule::integer(),
            'filter.with-product'       => 'array',
            'filter.with-product.*'     => 'string',
            'filter.without-product'    => 'array',
            'filter.without-product.*'  => 'string',
            'filter.in-product.*'       => JsonApiRule::integer(),
            'filter.not-in-product.*'   => JsonApiRule::integer(),
            'filter.with-currency'      => 'array',
            'filter.with-currency.*'    => 'string',
            'filter.without-currency'   => 'array',
            'filter.without-currency.*' => 'string',
            'filter.in-currency.*'      => JsonApiRule::integer(),
            'filter.not-in-currency.*'  => JsonApiRule::integer(),
            'include'                   => [
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
