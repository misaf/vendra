<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class CurrencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->badge()
                    ->label(__('vendra-currency::attributes.code')),
                TextEntry::make('name')->label(__('vendra-currency::attributes.name')),
                TextEntry::make('symbol')
                    ->label(__('vendra-currency::attributes.symbol'))
                    ->placeholder('—'),
                TextEntry::make('type')
                    ->badge()
                    ->label(__('vendra-currency::attributes.type')),
                TextEntry::make('decimal_places')
                    ->label(__('vendra-currency::attributes.decimal_places')),
                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-currency::attributes.active')),
                IconEntry::make('is_default')
                    ->boolean()
                    ->label(__('vendra-currency::attributes.is_default')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-currency::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
