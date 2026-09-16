<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraWishlist\Models\Wishlist;

final class WishlistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
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
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
