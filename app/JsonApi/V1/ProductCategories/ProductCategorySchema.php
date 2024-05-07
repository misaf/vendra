<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductCategories;

use App\Models;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;

final class ProductCategorySchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Models\Product\ProductCategory::class;

    protected ?array $defaultPagination = ['number' => 1];

    public function fields(): array
    {
        return [
            Fields\ID::make(),

            Fields\ArrayHash::make('name'),

            Fields\ArrayHash::make('description'),

            Fields\ArrayHash::make('slug'),

            Fields\Number::make('position')
                ->sortable()
                ->readOnly(),

            Fields\Boolean::make('status'),

            Fields\DateTime::make('created_at')
                ->sortable()
                ->readOnly(),

            Fields\DateTime::make('updated_at')
                ->sortable()
                ->readOnly(),

            Fields\Relations\HasMany::make('productPrices')
                ->readOnly(),

            Fields\Relations\BelongsToMany::make('multimedia')
                ->readOnly(),

            Fields\Relations\HasMany::make('products')
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),

            Filters\WhereIdNotIn::make($this, 'exclude'),

            Filters\Where::make('slug', 'slug->fa'),

            Filters\Where::make('status')
                ->asBoolean(),

            Filters\Has::make($this, 'hasProductPrices'),

            Filters\Has::make($this, 'hasMultimedia'),

            Filters\Has::make($this, 'hasProducts'),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'multimedia',
            'productPrices',
            'products.latestProductPrice',
            'products.multimedia',
            'products',
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Pagination\PagePagination
     */
    public function pagination(): Pagination\PagePagination
    {
        return Pagination\PagePagination::make();
    }
}
