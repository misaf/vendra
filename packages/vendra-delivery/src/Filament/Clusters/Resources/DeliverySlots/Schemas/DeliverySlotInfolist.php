<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class DeliverySlotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('vendra-delivery::attributes.name')),
                TextEntry::make('starts_at')
                    ->label(__('vendra-delivery::attributes.starts_at')),
                TextEntry::make('ends_at')
                    ->label(__('vendra-delivery::attributes.ends_at')),
                TextEntry::make('capacity')
                    ->label(__('vendra-delivery::attributes.capacity'))
                    ->placeholder('∞'),
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
