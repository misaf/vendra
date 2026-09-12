<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraTransaction\Actions\DeclineTransactionAction;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Declined;

final class DeclineTransactionTableAction
{
    public static function make(): Action
    {
        return Action::make('decline')
            ->authorize(fn (Transaction $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('danger')
            ->icon(Heroicon::OutlinedXCircle)
            ->label(__('vendra-transaction::messages.decline'))
            ->requiresConfirmation()
            ->visible(fn (Transaction $record): bool => $record->status->canTransitionTo(Declined::class))
            ->action(function (Transaction $record): void {
                resolve(DeclineTransactionAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-transaction::messages.transaction_declined'))
                    ->send();
            });
    }
}
