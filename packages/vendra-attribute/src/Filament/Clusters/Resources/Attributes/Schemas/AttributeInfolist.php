<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\TagIntegration;

final class AttributeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('name')
                ->label(__('vendra-attribute::attributes.name')),

            TextEntry::make('unit')
                ->badge()
                ->label(__('vendra-attribute::attributes.unit'))
                ->placeholder('-'),

            IconEntry::make('active')
                ->boolean()
                ->label(__('vendra-attribute::attributes.active')),

            TextEntry::make('values_count')
                ->badge()
                ->label(__('vendra-attribute::attributes.values'))
                ->state(fn (Attribute $record): int => $record->values()->count()),

            TextEntry::make('description')
                ->columnSpanFull()
                ->label(__('vendra-attribute::attributes.description'))
                ->placeholder('-'),

            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsEntry::make('tags')
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->type(Attribute::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-attribute::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
