<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

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
                TextEntry::make('address.line_one')
                    ->label(__('vendra-delivery::attributes.address'))
                    ->placeholder('—'),
                self::dateEntry('scheduled_for'),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-delivery::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDate('Y-m-d', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->date('Y-m-d'),
            );
    }
}
