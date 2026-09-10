<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\MultimediaResource;

final class ListMultimedia extends ListRecords
{
    protected static string $resource = MultimediaResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-multimedia::navigation.multimedia');
    }
}
