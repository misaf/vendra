<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Clusters\Resources\Wishlists\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Forms\Components\IsDefaultToggle;

final class WishlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-wishlist::navigation.wishlist'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('vendra-wishlist::attributes.name')),

                        TextInput::make('owner_label')
                            ->label(__('vendra-wishlist::attributes.owner')),

                        TextInput::make('token')
                            ->label(__('vendra-wishlist::attributes.token')),

                        IsDefaultToggle::make(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
