<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraOrder\Actions\CompleteOrderAction;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\Completed;

final class CompleteOrderTableAction
{
    public static function make(): Action
    {
        return Action::make('complete')
            ->authorize(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('success')
            ->icon(Heroicon::OutlinedTruck)
            ->label(__('vendra-order::messages.complete'))
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->status->canTransitionTo(Completed::class))
            ->action(function (Order $record): void {
                resolve(CompleteOrderAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-order::messages.order_completed'))
                    ->send();
            });
    }
}
