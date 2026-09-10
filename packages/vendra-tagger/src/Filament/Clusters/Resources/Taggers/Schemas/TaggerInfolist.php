<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Filament\Clusters\Resources\Taggers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class TaggerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('vendra-tagger::attributes.name')),
                TextEntry::make('slug')->label(__('vendra-tagger::attributes.slug')),
                TextEntry::make('type')
                    ->badge()
                    ->label(__('vendra-tagger::attributes.type')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-tagger::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
