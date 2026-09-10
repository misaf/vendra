<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraTransaction\Actions\SetDefaultTransactionGatewayAction;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class SetDefaultTransactionGatewayTableAction
{
    public static function make(): Action
    {
        return Action::make('setDefault')
            ->action(function (Action $action, TransactionGateway $record, SetDefaultTransactionGatewayAction $setDefaultTransactionGateway): void {
                $setDefaultTransactionGateway->execute($record);
                $action->success();
            })
            ->authorize(fn (TransactionGateway $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-transaction::actions.set_default'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('vendra-transaction::messages.default_gateway_updated'))
            ->visible(fn (TransactionGateway $record): bool => ! $record->is_default);
    }
}
