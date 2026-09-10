<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\DeliverySlotResource;

final class EditDeliverySlot extends EditRecord
{
    use Translatable;

    protected static string $resource = DeliverySlotResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-delivery::navigation.delivery_slot');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),

            LocaleSwitcher::make(),
        ];
    }
}
