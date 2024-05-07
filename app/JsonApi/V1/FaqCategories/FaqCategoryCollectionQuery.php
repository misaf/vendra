<?php

declare(strict_types=1);

namespace App\JsonApi\V1\FaqCategories;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

final class FaqCategoryCollectionQuery extends ResourceQuery
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
            'filter.id'                   => 'array',
            'filter.id.*'                 => JsonApiRule::integer(),
            'filter.exclude'              => 'array',
            'filter.exclude.*'            => JsonApiRule::integer(),
            'filter.slug'                 => 'string',
            'filter.status'               => JsonApiRule::boolean(),
            'filter.has-faqs'             => JsonApiRule::boolean(),
            'filter.with-faqs'            => 'array',
            'filter.with-faqs.*'          => 'string',
            'filter.without-faqs'         => 'array',
            'filter.without-faqs.*'       => 'string',
            'filter.has-multimedia'       => JsonApiRule::boolean(),
            'filter.with-multimedia'      => 'array',
            'filter.with-multimedia.*'    => 'string',
            'filter.without-multimedia'   => 'array',
            'filter.without-multimedia.*' => 'string',
            'include'                     => [
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
