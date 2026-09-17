<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class DeliveryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order.number')
                    ->copyable()
                    ->label(__('vendra-delivery::attributes.order')),
                TextEntry::make('recipient_name')
                    ->label(__('vendra-delivery::attributes.recipient_name'))
                    ->placeholder('—'),
                TextEntry::make('deliveryZone.name')
                    ->label(__('vendra-delivery::attributes.delivery_zone'))
                    ->placeholder('—'),
                TextEntry::make('deliverySlot.name')
                    ->label(__('vendra-delivery::attributes.delivery_slot'))
                    ->placeholder('—'),
                TextEntry::make('distance_km')
                    ->label(__('vendra-delivery::attributes.distance_km'))
                    ->placeholder('—'),
                TextEntry::make('fee_amount')
                    ->label(__('vendra-delivery::attributes.fee_amount')),
                IconEntry::make('requires_quote')
                    ->boolean()
                    ->label(__('vendra-delivery::attributes.requires_quote')),
                TextEntry::make('address.line_one')
                    ->label(__('vendra-delivery::attributes.address'))
                    ->placeholder('—'),
                TextEntry::make('scheduled_for')
                    ->label(__('vendra-delivery::attributes.scheduled_for'))
                    ->when(
                        app()->isLocale('fa'),
                        fn (TextEntry $entry): TextEntry => $entry->jalaliDate('Y-m-d', latinNumbers: true),
                        fn (TextEntry $entry): TextEntry => $entry->date('Y-m-d'),
                    ),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
