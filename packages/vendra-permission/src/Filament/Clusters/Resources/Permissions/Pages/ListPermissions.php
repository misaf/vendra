<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;

final class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-permission::navigation.permission');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
