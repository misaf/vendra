<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraCart\Models\Cart;

final class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('token')
                    ->copyable()
                    ->label(__('vendra-cart::attributes.token')),
                TextEntry::make('owner_label')
                    ->label(__('vendra-cart::attributes.owner')),
                TextEntry::make('items_count')
                    ->badge()
                    ->label(__('vendra-cart::attributes.items'))
                    ->state(fn (Cart $record): int => $record->items()->count()),
                self::dateEntry('expires_at'),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-cart::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
