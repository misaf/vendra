<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\Transactions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraTransaction\Actions\ApproveTransactionAction;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Approved;

final class ApproveTransactionTableAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->authorize(fn (Transaction $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-transaction::messages.approve'))
            ->requiresConfirmation()
            ->visible(fn (Transaction $record): bool => $record->status->canTransitionTo(Approved::class))
            ->action(function (Transaction $record): void {
                try {
                    resolve(ApproveTransactionAction::class)->execute($record);

                    Notification::make()
                        ->success()
                        ->title(__('vendra-transaction::messages.transaction_approved'))
                        ->send();
                } catch (InsufficientBalanceException) {
                    Notification::make()
                        ->danger()
                        ->title(__('vendra-transaction::messages.insufficient_balance'))
                        ->send();
                }
            });
    }
}
