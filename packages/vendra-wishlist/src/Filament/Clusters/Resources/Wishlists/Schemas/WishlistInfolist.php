<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraWishlist\Models\Wishlist;

final class WishlistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('vendra-wishlist::attributes.name')),
                TextEntry::make('owner_label')
                    ->label(__('vendra-wishlist::attributes.owner'))
                    ->placeholder('—'),
                TextEntry::make('token')
                    ->copyable()
                    ->label(__('vendra-wishlist::attributes.token')),
                IconEntry::make('is_default')
                    ->boolean()
                    ->label(__('vendra-wishlist::attributes.is_default')),
                TextEntry::make('items_count')
                    ->badge()
                    ->label(__('vendra-wishlist::attributes.items'))
                    ->state(fn (Wishlist $record): int => $record->items()->count()),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-wishlist::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
