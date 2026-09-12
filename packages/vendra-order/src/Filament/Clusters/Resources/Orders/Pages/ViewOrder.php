<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\CancelOrderTableAction;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\CompleteOrderTableAction;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions\ConfirmOrderTableAction;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\OrderResource;

final class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ConfirmOrderTableAction::make(),
            CompleteOrderTableAction::make(),
            CancelOrderTableAction::make(),
            DeleteAction::make(),
        ];
    }
}
