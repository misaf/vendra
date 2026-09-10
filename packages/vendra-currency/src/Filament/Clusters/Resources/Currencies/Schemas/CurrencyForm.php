<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraCurrency\Support\CurrencyRegistry;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('code')
                    ->afterStateUpdated(function (Livewire $livewire, Set $set, ?string $state): void {
                        $livewire->validateOnly('data.code');

                        if ($state === null || ! CurrencyRegistry::isSupported($state)) {
                            return;
                        }

                        $set('name', CurrencyRegistry::nameFor($state));
                        $set('decimal_places', CurrencyRegistry::minorUnitFor($state));
                        $set('type', CurrencyRegistry::typeFor($state)?->value);
                    })
                    ->label(__('vendra-currency::attributes.code'))
                    ->live()
                    ->native(false)
                    ->options(fn (?Currency $record): array => self::installableCurrencyOptions($record))
                    ->required()
                    ->rule(Rule::in(CurrencyRegistry::codes()))
                    ->searchable()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule),
                    ),

                TextInput::make('name')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.name'))
                    ->label(__('vendra-currency::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required(),

                TextInput::make('symbol')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.symbol'))
                    ->helperText(__('vendra-currency::attributes.symbol_helper_text'))
                    ->label(__('vendra-currency::attributes.symbol'))
                    ->live(onBlur: true)
                    ->maxLength(16),

                TextInput::make('decimal_places')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.decimal_places'))
                    ->dehydrated()
                    ->disabled(fn (Get $get): bool => CurrencyType::Fiat->value === $get('type'))
                    ->helperText(__('vendra-currency::messages.decimal_places_fiat_hint'))
                    ->integer()
                    ->label(__('vendra-currency::attributes.decimal_places'))
                    ->live(onBlur: true)
                    ->maxValue(255)
                    ->minValue(0)
                    ->required(),

                Select::make('type')
                    ->default(CurrencyType::Fiat->value)
                    ->dehydrated()
                    ->disabled()
                    ->label(__('vendra-currency::attributes.type'))
                    ->native(false)
                    ->options(CurrencyType::class)
                    ->required(),

                Toggle::make('active')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.active'))
                    ->columnSpanFull()
                    ->default(true)
                    ->label(__('vendra-currency::attributes.active'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules(['boolean']),

                Toggle::make('is_default')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.is_default'))
                    ->columnSpanFull()
                    ->default(false)
                    ->helperText(__('vendra-currency::attributes.is_default_helper_text'))
                    ->label(__('vendra-currency::attributes.is_default'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules(['boolean']),
            ])
            ->columns(2);
    }

    /** @return array<string, string> */
    private static function installableCurrencyOptions(?Currency $record): array
    {
        $installedCurrenciesQuery = Currency::query();

        if ($record !== null) {
            $installedCurrenciesQuery->whereKeyNot($record->getKey());
        }

        $installedCodes = $installedCurrenciesQuery
            ->get(['code'])
            ->map(fn (Currency $currency): string => $currency->code);

        return collect(CurrencyRegistry::options())
            ->except($installedCodes)
            ->all();
    }
}
