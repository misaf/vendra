<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;

final class CreateUserProfile extends CreateRecord
{
    protected static string $resource = UserProfileResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-user-profile::navigation.user_profile');
    }
}
