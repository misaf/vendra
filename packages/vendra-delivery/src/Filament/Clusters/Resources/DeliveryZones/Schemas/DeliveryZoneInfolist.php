<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class DeliveryZoneInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('vendra-delivery::attributes.name')),
                TextEntry::make('description')
                    ->label(__('vendra-delivery::attributes.description'))
                    ->placeholder('—'),
                TextEntry::make('origin_latitude')
                    ->label(__('vendra-delivery::attributes.origin_latitude')),
                TextEntry::make('origin_longitude')
                    ->label(__('vendra-delivery::attributes.origin_longitude')),
                TextEntry::make('max_distance_km')
                    ->label(__('vendra-delivery::attributes.max_distance_km'))
                    ->placeholder('—'),
                TextEntry::make('fee_amount')
                    ->label(__('vendra-delivery::attributes.fee_amount')),
                IconEntry::make('requires_quote')
                    ->boolean()
                    ->label(__('vendra-delivery::attributes.requires_quote')),
                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-delivery::attributes.active')),
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
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
