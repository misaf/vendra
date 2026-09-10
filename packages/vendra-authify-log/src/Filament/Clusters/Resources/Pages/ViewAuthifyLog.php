<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\AuthifyLogResource;

final class ViewAuthifyLog extends ViewRecord
{
    protected static string $resource = AuthifyLogResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-authify-log::navigation.authify_log');
    }
}
