<?php

declare(strict_types=1);

namespace App\JsonApi\V1\BlogPostCategories;

use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;
use Termehsoft\Blog\Models\BlogPostCategory;

final class BlogPostCategorySchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = BlogPostCategory::class;

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
            Fields\Relations\HasMany::make('blogPosts')
                ->readOnly(),
            Fields\Relations\BelongsToMany::make('multimedia')
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
            Filters\WhereIdNotIn::make($this, 'exclude'),
            Filters\Where::make('slug', 'slug->fa')
                ->singular(),
            Filters\Where::make('status')
                ->asBoolean(),
            Filters\Has::make($this, 'blogPosts', 'has-blog-posts'),
            Filters\WhereHas::make($this, 'blogPosts', 'with-blog-posts'),
            Filters\WhereDoesntHave::make($this, 'blogPosts', 'without-blog-posts'),
            Filters\Has::make($this, 'multimedia', 'has-multimedia'),
            Filters\WhereHas::make($this, 'multimedia', 'with-multimedia'),
            Filters\WhereDoesntHave::make($this, 'multimedia', 'without-multimedia'),
            Filters\WithTrashed::make('with-trashed'),
            Filters\OnlyTrashed::make('trashed'),
        ];
    }

    public function includePaths(): iterable
    {
        return [
            'blogPosts',
            'multimedia',
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
