<?php

declare(strict_types=1);

namespace App\JsonApi\V1\ProductPrices;

use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;
use Termehsoft\Product\Models\ProductPrice;

final class ProductPriceSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = ProductPrice::class;

    protected ?array $defaultPagination = ['number' => 1];

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\ArrayHash::make('price'),
            Fields\DateTime::make('created_at')
                ->sortable()
                ->readOnly(),
            Fields\DateTime::make('updated_at')
                ->sortable()
                ->readOnly(),
            Fields\Relations\BelongsTo::make('product')
                ->readOnly(),
            Fields\Relations\BelongsTo::make('currency')
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
            Filters\WhereIdNotIn::make($this, 'exclude'),
            Filters\Where::make('product', 'product_id'),
            Filters\Where::make('currency', 'currency_id'),
            Filters\WhereHas::make($this, 'product', 'with-product'),
            Filters\WhereDoesntHave::make($this, 'product', 'without-product'),
            Filters\WhereIn::make('in-product', 'product_id'),
            Filters\WhereNotIn::make('not-in-product', 'product_id'),
            Filters\WhereHas::make($this, 'currency', 'with-currency'),
            Filters\WhereDoesntHave::make($this, 'currency', 'without-currency'),
            Filters\WhereIn::make('in-currency', 'currency_id'),
            Filters\WhereNotIn::make('not-in-currency', 'currency_id'),
            Filters\WithTrashed::make('with-trashed'),
            Filters\OnlyTrashed::make('trashed'),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'currency',
            'product',
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
