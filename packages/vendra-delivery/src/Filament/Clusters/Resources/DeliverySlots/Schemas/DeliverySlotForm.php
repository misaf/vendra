<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;

final class DeliverySlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-delivery::navigation.delivery_slot'))
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->label(__('vendra-delivery::attributes.name'))
                            ->maxLength(255)
                            ->required(),

                        Grid::make(2)->schema([
                            TimePicker::make('starts_at')
                                ->columnSpan(1)
                                ->label(__('vendra-delivery::attributes.starts_at'))
                                ->required()
                                ->seconds(false),

                            TimePicker::make('ends_at')
                                ->after('starts_at')
                                ->columnSpan(1)
                                ->label(__('vendra-delivery::attributes.ends_at'))
                                ->required()
                                ->seconds(false),
                        ]),

                        TextInput::make('capacity')
                            ->helperText(__('vendra-delivery::attributes.capacity_hint'))
                            ->label(__('vendra-delivery::attributes.capacity'))
                            ->minValue(1)
                            ->numeric(),

                        IsActiveToggle::make()
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
