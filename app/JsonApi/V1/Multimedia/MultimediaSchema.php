<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Multimedia;

use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters;
use LaravelJsonApi\Eloquent\Pagination;
use LaravelJsonApi\Eloquent\Schema;
use Spatie\MediaLibrary\MediaCollections as Spatie;

final class MultimediaSchema extends Schema
{
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Spatie\Models\Media::class;

    protected ?array $defaultPagination = ['number' => 1];

    public function authorizable(): bool
    {
        return false;
    }

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\Str::make('uuid')
                ->readOnly(),
            Fields\Str::make('collection_name')
                ->readOnly(),
            Fields\Str::make('name')
                ->readOnly(),
            Fields\Str::make('file_name')
                ->readOnly(),
            Fields\Str::make('mime_type')
                ->readOnly(),
            Fields\Str::make('disk')
                ->readOnly(),
            Fields\Str::make('conversions_disk')
                ->readOnly(),
            Fields\Number::make('size')
                ->readOnly(),
            Fields\Str::make('manipulations')
                ->readOnly(),
            Fields\Str::make('custom_properties')
                ->readOnly(),
            Fields\ArrayHash::make('generated_conversions')
                ->readOnly(),
            Fields\ArrayHash::make('responsive_images')
                ->readOnly(),
            Fields\Number::make('order_column')
                ->sortable()
                ->readOnly(),
            Fields\DateTime::make('created_at')
                ->sortable()
                ->readOnly(),
            Fields\DateTime::make('updated_at')
                ->sortable()
                ->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            Filters\WhereIdIn::make($this),
            Filters\WhereIdNotIn::make($this, 'exclude'),
            Filters\Where::make('uuid')
                ->singular(),
            Filters\Where::make('collection-name', 'collection_name'),
            Filters\Where::make('name'),
            Filters\Where::make('file-name', 'file_name'),
            Filters\Where::make('mime-type', 'mime_type'),
            Filters\Where::make('disk'),
            Filters\Where::make('conversions-disk', 'conversions_disk'),
            Filters\Where::make('size'),
            Filters\Where::make('gt-size', 'size')
                ->gt(),
            Filters\Where::make('gte-size', 'size')
                ->gte(),
            Filters\Where::make('lt-size', 'size')
                ->lt(),
            Filters\Where::make('lte-size', 'size')
                ->lte(),
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
