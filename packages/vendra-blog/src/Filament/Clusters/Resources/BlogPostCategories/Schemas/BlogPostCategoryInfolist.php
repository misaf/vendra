<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPostCategories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;

final class BlogPostCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('vendra-blog::attributes.name')),

                TextEntry::make('slug')
                    ->label(__('vendra-blog::attributes.slug')),

                TextEntry::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-blog::attributes.description')),

                IconEntry::make('active')
                    ->boolean()
                    ->label(__('vendra-blog::attributes.active')),

                ModelImageEntry::make()
                    ->collection(BlogPostCategory::MEDIA_COLLECTION),

                self::dateEntry('created_at'),
                self::dateEntry('updated_at'),
            ])
            ->columns(2);
    }

    private static function dateEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__("vendra-blog::attributes.{$name}"))
            ->when(
                app()->isLocale('fa'),
                fn (TextEntry $entry): TextEntry => $entry->jalaliDateTime('Y-m-d H:i', latinNumbers: true),
                fn (TextEntry $entry): TextEntry => $entry->dateTime('Y-m-d H:i'),
            );
    }
}
