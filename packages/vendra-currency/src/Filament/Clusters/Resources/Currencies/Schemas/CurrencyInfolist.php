<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsDefaultEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class CurrencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->badge()
                    ->label(__('vendra-currency::attributes.code')),
                NameEntry::make(),
                TextEntry::make('symbol')
                    ->label(__('vendra-currency::attributes.symbol'))
                    ->placeholder('—'),
                TextEntry::make('type')
                    ->badge()
                    ->label(__('vendra-currency::attributes.type')),
                TextEntry::make('decimal_places')
                    ->label(__('vendra-currency::attributes.decimal_places')),
                IsActiveEntry::make(),
                IsDefaultEntry::make(),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
