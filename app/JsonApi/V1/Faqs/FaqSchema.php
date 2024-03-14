<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Faqs;

use App\Models;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;

final class FaqSchema extends Schema
{
    public static string $model = Models\Faq\Faq::class;

    protected $defaultSort = ['-position'];

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\ArrayHash::make('name'),
            Fields\ArrayHash::make('description'),
            Fields\ArrayHash::make('slug'),
            Fields\Number::make('position')->sortable()->readOnly(),
            Fields\Boolean::make('status'),
            Fields\DateTime::make('createdAt')->sortable()->readOnly(),
            Fields\DateTime::make('updatedAt')->sortable()->readOnly(),
            Fields\Relations\BelongsTo::make('faqCategory')->readOnly(),
            Fields\Relations\BelongsToMany::make('multimedia')->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
            Filters\where::make('slug', 'slug->fa'),
            Filters\where::make('status')->asBoolean(),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'faqCategory',
            'multimedia',
        ];
    }

    public function pagination(): ?Paginator
    {
        return Pagination\PagePagination::make();
    }
}
