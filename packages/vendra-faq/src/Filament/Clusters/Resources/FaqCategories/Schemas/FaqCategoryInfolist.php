<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraFaq\Models\FaqCategory;

final class FaqCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label(__('vendra-faq::attributes.name')),
                TextEntry::make('slug')->label(__('vendra-faq::attributes.slug')),
                TextEntry::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-faq::attributes.description')),
                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-faq::attributes.active')),
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection(FaqCategory::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->label(__('vendra-faq::attributes.image')),
                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-faq::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
