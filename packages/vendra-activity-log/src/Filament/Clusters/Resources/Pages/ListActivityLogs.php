<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\ActivityLogResource;

final class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-activity-log::navigation.activity_log');
    }
}
