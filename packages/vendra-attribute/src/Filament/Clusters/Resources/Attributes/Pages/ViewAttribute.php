<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\AttributeResource;

final class ViewAttribute extends ViewRecord
{
    protected static string $resource = AttributeResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-attribute::navigation.attribute');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
