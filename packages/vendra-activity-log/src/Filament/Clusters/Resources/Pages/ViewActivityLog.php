<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraActivityLog\Filament\Clusters\Resources\ActivityLogResource;

final class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-activity-log::navigation.activity_log');
    }
}
