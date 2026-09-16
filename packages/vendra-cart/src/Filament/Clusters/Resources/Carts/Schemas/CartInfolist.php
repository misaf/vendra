<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

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
                DateTimeEntry::make('expires_at')
                    ->label(__('vendra-cart::attributes.expires_at')),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
