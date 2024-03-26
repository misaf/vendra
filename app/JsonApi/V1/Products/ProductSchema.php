<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Products;

use App\Models;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;

final class ProductSchema extends Schema
{
    public static string $model = Models\Product\Product::class;

    protected $defaultSort = ['-position'];

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\Str::make('name'),
            Fields\Str::make('description'),
            Fields\Str::make('slug'),
            Fields\Number::make('token'),
            Fields\Number::make('quantity'),
            Fields\Number::make('stock_threshold'),
            Fields\Boolean::make('in_stock'),
            Fields\Number::make('position')->sortable()->readOnly(),
            Fields\DateTime::make('available_soon')->sortable(),
            Fields\DateTime::make('availability_date')->sortable(),
            Fields\DateTime::make('createdAt')->sortable()->readOnly(),
            Fields\DateTime::make('updatedAt')->sortable()->readOnly(),
            Fields\Relations\BelongsTo::make('productCategory')->readOnly(),
            Fields\Relations\HasMany::make('productPrices')->readOnly(),
            Fields\Relations\BelongsToMany::make('multimedia')->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
            Filters\Where::make('product_category_id'),
            Filters\Where::make('name', 'name->fa'),
            Filters\Where::make('slug', 'slug->fa'),
            Filters\Where::make('token'),
            Filters\Where::make('in_stock')->asBoolean(),
            Filters\WhereHas::make($this, 'productCategory'),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'multimedia',
            'productCategory',
            'productPrices',
        ];
    }

    public function pagination(): ?Paginator
    {
        return Pagination\PagePagination::make();
    }
}
