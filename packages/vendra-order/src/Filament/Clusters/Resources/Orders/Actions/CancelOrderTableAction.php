<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraOrder\Actions\CancelOrderAction;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\Cancelled;

final class CancelOrderTableAction
{
    public static function make(): Action
    {
        return Action::make('cancel')
            ->authorize(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('danger')
            ->icon(Heroicon::OutlinedXCircle)
            ->label(__('vendra-order::messages.cancel'))
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->status->canTransitionTo(Cancelled::class))
            ->action(function (Order $record): void {
                resolve(CancelOrderAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-order::messages.order_cancelled'))
                    ->send();
            });
    }
}
