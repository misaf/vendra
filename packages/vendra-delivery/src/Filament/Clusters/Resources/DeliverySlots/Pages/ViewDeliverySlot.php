<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\DeliverySlotResource;

final class ViewDeliverySlot extends ViewRecord
{
    use Translatable;

    protected static string $resource = DeliverySlotResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/view-record.breadcrumb').' '.__('vendra-delivery::navigation.delivery_slot');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
