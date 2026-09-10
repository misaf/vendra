<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;

final class FaqInfolist
{
    use RendersRichContent;

    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('faqCategory.name')
                ->label(__('vendra-faq::navigation.faq_category')),
            TextEntry::make('name')->label(__('vendra-faq::attributes.name')),
            TextEntry::make('slug')->label(__('vendra-faq::attributes.slug')),
            IconEntry::make('active')
                ->boolean()
                ->label(__('vendra-faq::attributes.active')),
            TextEntry::make('description')
                ->columnSpanFull()
                ->formatStateUsing(fn (array|string|null $state): string => self::renderRichContent($state))
                ->html()
                ->label(__('vendra-faq::attributes.description')),
            SpatieMediaLibraryImageEntry::make('image')
                ->collection(Faq::MEDIA_COLLECTION)
                ->columnSpanFull()
                ->label(__('vendra-faq::attributes.image')),
            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = SpatieTagsEntry::make('tags')
                ->columnSpanFull()
                ->label(__('vendra-support::attributes.tags'))
                ->type(Faq::TAG_TYPE);
        }

        return $schema
            ->components($components)
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
