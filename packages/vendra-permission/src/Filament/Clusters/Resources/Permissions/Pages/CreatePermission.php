<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;

final class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-permission::navigation.permission');
    }
}
