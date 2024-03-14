<?php

declare(strict_types=1);

namespace App\JsonApi\V1\Multimedia;

use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use Spatie\MediaLibrary\MediaCollections as Spatie;

final class MultimediaSchema extends Schema
{
    public static string $model = Spatie\Models\Media::class;

    public function authorizable(): bool
    {
        return false;
    }

    public function fields(): array
    {
        return [
            Fields\ID::make(),
            Fields\Str::make('uuid')->readOnly(),
            Fields\Str::make('collection_name')->readOnly(),
            Fields\Str::make('name')->readOnly(),
            Fields\Str::make('file_name')->readOnly(),
            Fields\Str::make('mime_type')->readOnly(),
            Fields\Str::make('disk')->readOnly(),
            Fields\Str::make('conversions_disk')->readOnly(),
            Fields\Number::make('size')->readOnly(),
            Fields\Str::make('manipulations')->readOnly(),
            Fields\Str::make('custom_properties')->readOnly(),
            Fields\ArrayHash::make('generated_conversions')->readOnly(),
            Fields\ArrayHash::make('responsive_images')->readOnly(),
            Fields\Number::make('order_column')->sortable()->readOnly(),
            Fields\DateTime::make('createdAt')->sortable()->readOnly(),
            Fields\DateTime::make('updatedAt')->sortable()->readOnly(),
        ];
    }

    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
        ];
    }

    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
