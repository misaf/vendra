<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraOrder\Actions\ConfirmOrderAction;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraOrder\States\Confirmed;

final class ConfirmOrderTableAction
{
    public static function make(): Action
    {
        return Action::make('confirm')
            ->authorize(fn (Order $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-order::messages.confirm'))
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->status->canTransitionTo(Confirmed::class))
            ->action(function (Order $record): void {
                resolve(ConfirmOrderAction::class)->execute($record);

                Notification::make()
                    ->success()
                    ->title(__('vendra-order::messages.order_confirmed'))
                    ->send();
            });
    }
}
