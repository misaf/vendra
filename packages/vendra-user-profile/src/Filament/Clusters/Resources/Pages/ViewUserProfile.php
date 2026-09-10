<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Filament\Clusters\Resources\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraUserProfile\Filament\Clusters\Resources\UserProfileResource;

final class ViewUserProfile extends ViewRecord
{
    protected static string $resource = UserProfileResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-user-profile::navigation.user_profile');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
