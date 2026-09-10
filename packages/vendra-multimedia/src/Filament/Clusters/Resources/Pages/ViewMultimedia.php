<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraMultimedia\Filament\Clusters\Resources\MultimediaResource;

final class ViewMultimedia extends ViewRecord
{
    protected static string $resource = MultimediaResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-multimedia::navigation.multimedia');
    }
}
