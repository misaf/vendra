<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;

final class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/create-record.breadcrumb').' '.__('vendra-currency::navigation.currency');
    }
}
