<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;

final class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/edit-record.breadcrumb').' '.__('vendra-currency::navigation.currency');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),
        ];
    }
}
