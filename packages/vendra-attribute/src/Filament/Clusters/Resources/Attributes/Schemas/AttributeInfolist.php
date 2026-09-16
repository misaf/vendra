<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;

final class AttributeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            NameEntry::make(),

            TextEntry::make('unit')
                ->badge()
                ->label(__('vendra-attribute::attributes.unit'))
                ->placeholder('-'),

            IsActiveEntry::make(),

            TextEntry::make('values_count')
                ->badge()
                ->label(__('vendra-attribute::attributes.values'))
                ->state(fn (Attribute $record): int => $record->values()->count()),

            DescriptionEntry::make()
                ->placeholder('-'),

            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsEntry::make()
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
