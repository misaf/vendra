<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Actions;

use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraCurrency\Actions\SetDefaultCurrencyAction;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\CurrencyResource;
use Misaf\VendraCurrency\Models\Currency;

final class SetDefaultCurrencyTableAction
{
    /**
     * @param  class-string<resource>  $resource
     */
    public static function make(string $resource = CurrencyResource::class): Action
    {
        return Action::make('setDefault')
            ->action(function (Action $action, Currency $record, SetDefaultCurrencyAction $setDefaultCurrency): void {
                $setDefaultCurrency->execute($record);
                $action->success();
            })
            ->authorize(fn (Currency $record): bool => $resource::canEdit($record))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->label(__('vendra-currency::actions.set_default'))
            ->requiresConfirmation()
            ->successNotificationTitle(__('vendra-currency::messages.default_currency_updated'))
            ->visible(fn (Currency $record): bool => ! $record->is_default);
    }
}
