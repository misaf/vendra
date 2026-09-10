<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;

final class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-delivery::navigation.delivery_zone'))
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->label(__('vendra-delivery::attributes.name'))
                            ->maxLength(255)
                            ->required(),

                        Textarea::make('description')
                            ->columnSpanFull()
                            ->label(__('vendra-delivery::attributes.description'))
                            ->rows(2),

                        Grid::make(2)->schema([
                            TextInput::make('origin_latitude')
                                ->columnSpan(1)
                                ->label(__('vendra-delivery::attributes.origin_latitude'))
                                ->numeric()
                                ->required()
                                ->rules(['numeric', 'between:-90,90']),

                            TextInput::make('origin_longitude')
                                ->columnSpan(1)
                                ->label(__('vendra-delivery::attributes.origin_longitude'))
                                ->numeric()
                                ->required()
                                ->rules(['numeric', 'between:-180,180']),
                        ]),

                        TextInput::make('max_distance_km')
                            ->helperText(__('vendra-delivery::attributes.max_distance_km_hint'))
                            ->label(__('vendra-delivery::attributes.max_distance_km'))
                            ->numeric()
                            ->minValue(0),

                        Toggle::make('requires_quote')
                            ->helperText(__('vendra-delivery::attributes.requires_quote_hint'))
                            ->label(__('vendra-delivery::attributes.requires_quote'))
                            ->live(),

                        Select::make('currency_code')
                            ->label(__('vendra-delivery::attributes.currency_code'))
                            ->options(CurrencyIntegration::options())
                            ->default(Str::upper(CurrencyIntegration::defaultCode()))
                            ->required(),

                        TextInput::make('fee_amount')
                            ->disabled(fn (Get $get): bool => (bool) $get('requires_quote'))
                            ->helperText(__('vendra-delivery::attributes.fee_amount_hint'))
                            ->label(__('vendra-delivery::attributes.fee_amount'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Toggle::make('active')
                            ->label(__('vendra-delivery::attributes.active')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
