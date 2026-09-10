<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\Pages;

use Filament\Resources\Pages\ListRecords;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Wallets\WalletResource;

final class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    public function getBreadcrumb(): string
    {
        return self::$breadcrumb ?? __('filament-panels::resources/pages/list-records.breadcrumb').' '.__('vendra-transaction::navigation.wallet');
    }
}
