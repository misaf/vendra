<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\DeliveryZoneResource;

final class ListDeliveryZones extends ListRecords
{
    use Translatable;

    protected static string $resource = DeliveryZoneResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-delivery::navigation.delivery_zone');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
