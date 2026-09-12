<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Misaf\VendraCurrency\Actions\InstallCurrenciesAction;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;

final class InstallCurrenciesTableAction
{
    public static function make(): Action
    {
        return Action::make('installCurrencies')
            ->authorize(fn (): bool => auth()->user()?->can('create', Currency::class) ?? false)
            ->schema([
                Select::make('codes')
                    ->label(__('vendra-currency::attributes.code'))
                    ->multiple()
                    ->native(false)
                    ->options(fn (): array => self::uninstalledCurrencyOptions())
                    ->required()
                    ->searchable(),
            ])
            ->action(function (array $data): void {
                resolve(InstallCurrenciesAction::class)->execute((array) Arr::get($data, 'codes', []));
            })
            ->icon(Heroicon::OutlinedSquaresPlus)
            ->label(__('vendra-currency::actions.install_from_catalog'))
            ->successNotificationTitle(__('vendra-currency::messages.currencies_installed'));
    }

    /** @return array<string, string> */
    private static function uninstalledCurrencyOptions(): array
    {
        $installedCodes = Currency::query()
            ->get(['code'])
            ->map(fn (Currency $currency): string => $currency->code);

        return collect(CurrencyRegistry::options())
            ->except($installedCodes)
            ->all();
    }
}
