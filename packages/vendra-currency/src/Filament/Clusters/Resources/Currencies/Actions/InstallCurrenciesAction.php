<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Actions;

use Illuminate\Support\Arr;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;

final class InstallCurrenciesAction
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
                collect(Arr::get($data, 'codes', []))
                    ->filter(fn (mixed $code): bool => is_string($code) && CurrencyRegistry::isSupported($code))
                    ->reject(fn (string $code): bool => Currency::query()->where('code', mb_strtoupper($code))->exists())
                    ->each(fn (string $code) => Currency::query()->create([
                        'code' => $code,
                        'name' => CurrencyRegistry::nameFor($code),
                        'decimal_places' => CurrencyRegistry::minorUnitFor($code),
                        'type' => CurrencyRegistry::typeFor($code),
                    ]));
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
