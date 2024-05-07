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
            Filters\Where::make('product-category', 'product_category_id'),
            Filters\Where::make('slug', 'slug->fa')
                ->singular(),
            Filters\WhereIn::make('in-slug', 'slug->fa'),
            Filters\WhereNotIn::make('not-in-slug', 'slug->fa'),
            Filters\Where::make('token')
                ->singular(),
            Filters\WhereIn::make('in-token', 'token'),
            Filters\WhereNotIn::make('not-in-token', 'token'),
            Filters\Where::make('quantity'),
            Filters\Where::make('gt-quantity', 'quantity')
                ->gt(),
            Filters\Where::make('gte-quantity', 'quantity')
                ->gte(),
            Filters\Where::make('lt-quantity', 'quantity')
                ->lt(),
            Filters\Where::make('lte-quantity', 'quantity')
                ->lte(),
            Filters\Where::make('stock-threshold'),
            Filters\Where::make('gt-stock-threshold', 'stock_threshold')
                ->gt(),
            Filters\Where::make('gte-stock-threshold', 'stock_threshold')
                ->gte(),
            Filters\Where::make('lt-stock-threshold', 'stock_threshold')
                ->lt(),
            Filters\Where::make('lte-stock-threshold', 'stock_threshold')
                ->lte(),
            Filters\Where::make('in_stock')
                ->asBoolean(),
            Filters\Where::make('available_soon')
                ->asBoolean(),
            Filters\Where::make('availability_date'),
            Filters\WhereHas::make($this, 'latestProductPrice', 'with-latest-product-price'),
            Filters\WhereDoesntHave::make($this, 'latestProductPrice', 'without-latest-product-price'),
            Filters\Has::make($this, 'multimedia', 'has-multimedia'),
            Filters\WhereHas::make($this, 'multimedia', 'with-multimedia'),
            Filters\WhereDoesntHave::make($this, 'multimedia', 'without-multimedia'),
            Filters\WhereHas::make($this, 'productCategory', 'with-productCategory'),
            Filters\WhereDoesntHave::make($this, 'productCategory', 'without-productCategory'),
            Filters\WhereIn::make('in-productCategory', 'product_category_id'),
            Filters\WhereNotIn::make('not-in-productCategory', 'product_category_id'),
            Filters\Has::make($this, 'productPrices', 'has-product-prices'),
            Filters\WhereHas::make($this, 'productPrices', 'with-product-prices'),
            Filters\WhereDoesntHave::make($this, 'productPrices', 'without-product-prices'),
            Filters\WithTrashed::make('with-trashed'),
            Filters\OnlyTrashed::make('trashed'),
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
