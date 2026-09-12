<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\ApproveTransactionTableAction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\DeclineTransactionTableAction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions\FailTransactionTableAction;
use Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\TransactionResource;

final class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ApproveTransactionTableAction::make(),
            DeclineTransactionTableAction::make(),
            FailTransactionTableAction::make(),
        ];
    }
}
