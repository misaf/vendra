<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraTransaction\Actions\FailTransactionAction;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Failed;

final class FailTransactionTableAction
{
    public static function make(): Action
    {
        return Action::make('fail')
            ->authorize(fn (Transaction $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('gray')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->label(__('vendra-transaction::messages.fail'))
            ->requiresConfirmation()
            ->visible(fn (Transaction $record): bool => $record->status->canTransitionTo(Failed::class))
            ->action(function (Transaction $record): void {
                resolve(FailTransactionAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-transaction::messages.transaction_failed'))
                    ->send();
            });
    }
}
