<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductPrices;

use App\Models;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;

final class ProductPriceSchema extends Schema
{
    public static string $model = Models\Product\ProductPrice::class;

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\ArrayHash::make('price'),
            Fields\DateTime::make('createdAt')->sortable()->readOnly(),
            Fields\DateTime::make('updatedAt')->sortable()->readOnly(),
            Fields\Relations\BelongsTo::make('product')->readOnly(),
            Fields\Relations\BelongsTo::make('currency')->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'product',
            'currency',
        ];
    }

    public function pagination(): ?Paginator
    {
        return Pagination\PagePagination::make();
    }
}
