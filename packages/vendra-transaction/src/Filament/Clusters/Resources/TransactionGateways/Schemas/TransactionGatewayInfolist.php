<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Filament\Clusters\Resources\TransactionGateways\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraTransaction\Models\TransactionGateway;

final class TransactionGatewayInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('vendra-transaction::attributes.name')),
                TextEntry::make('slug')->label(__('vendra-transaction::attributes.slug')),
                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-transaction::attributes.active')),
                IconEntry::make('is_default')
                    ->boolean()
                    ->label(__('vendra-transaction::attributes.is_default')),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-transaction::attributes.description')),
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection(TransactionGateway::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->label(__('vendra-transaction::attributes.image')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-transaction::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
