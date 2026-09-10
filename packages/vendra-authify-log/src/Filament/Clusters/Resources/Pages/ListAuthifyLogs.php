<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Misaf\VendraAuthifyLog\Filament\Clusters\Resources\AuthifyLogResource;

final class ListAuthifyLogs extends ListRecords
{
    protected static string $resource = AuthifyLogResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-authify-log::navigation.authify_log');
    }
}
