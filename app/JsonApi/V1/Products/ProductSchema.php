<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Products;

use App\Models;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;

final class ProductSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Models\Product\Product::class;

    protected ?array $defaultPagination = ['number' => 1];

    public function fields(): array
    {
        return [
            Fields\ID::make(),

            Fields\Str::make('name'),

            Fields\Str::make('description'),

            Fields\Str::make('slug'),

            Fields\Number::make('token')
                ->readOnly(),

            Fields\Number::make('quantity'),

            Fields\Number::make('stock_threshold'),

            Fields\Boolean::make('in_stock'),

            Fields\Number::make('position')
                ->sortable()
                ->readOnly(),

            Fields\DateTime::make('available_soon')
                ->sortable(),

            Fields\DateTime::make('availability_date')
                ->sortable(),

            Fields\DateTime::make('created_at')
                ->sortable()
                ->readOnly(),

            Fields\DateTime::make('updated_at')
                ->sortable()
                ->readOnly(),

            Fields\Relations\HasOne::make('latestProductPrice')
                ->readOnly()
                ->type('product-prices'),

            Fields\Relations\BelongsToMany::make('multimedia')
                ->readOnly(),

            Fields\Relations\BelongsTo::make('productCategory')
                ->readOnly(),

            Fields\Relations\HasMany::make('productPrices')
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),

            Filters\WhereIdNotIn::make($this, 'exclude'),

            Filters\where::make('product_category_id'),

            Filters\Where::make('name', 'name->fa'),

            Filters\Where::make('slug', 'slug->fa'),

            Filters\Where::make('token'),

            Filters\Where::make('in_stock')
                ->asBoolean(),

            Filters\Has::make($this, 'hasMultimedia'),

            Filters\Has::make($this, 'hasProductPrices'),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'latestProductPrice',
            'multimedia',
            'productCategory',
            'productPrices',
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
