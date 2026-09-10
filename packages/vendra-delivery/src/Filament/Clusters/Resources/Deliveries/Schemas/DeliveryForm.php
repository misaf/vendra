<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraDelivery\Models\DeliverySlot;

final class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-delivery::navigation.delivery'))
                    ->schema([
                        DatePicker::make('scheduled_for')
                            ->label(__('vendra-delivery::attributes.scheduled_for')),

                        Select::make('delivery_slot_id')
                            ->label(__('vendra-delivery::attributes.delivery_slot'))
                            ->options(fn (): array => DeliverySlot::query()
                                ->active()
                                ->ordered()
                                ->pluck('name', 'id')
                                ->all()),

                        TextInput::make('recipient_name')
                            ->label(__('vendra-delivery::attributes.recipient_name'))
                            ->maxLength(255),

                        TextInput::make('fee_amount')
                            ->label(__('vendra-delivery::attributes.fee_amount')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
