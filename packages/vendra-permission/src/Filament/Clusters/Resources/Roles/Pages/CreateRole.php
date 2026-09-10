<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-permission::navigation.role');
    }
}
