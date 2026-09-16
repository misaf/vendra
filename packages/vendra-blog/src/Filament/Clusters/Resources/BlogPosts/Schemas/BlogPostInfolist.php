<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Concerns\RendersRichContent;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;

final class BlogPostInfolist
{
    use RendersRichContent;

    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('blogPostCategory.name')
                ->label(__('vendra-blog::navigation.blog_post_category')),

            TextEntry::make('name')
                ->label(__('vendra-blog::attributes.name')),

            TextEntry::make('slug')
                ->label(__('vendra-blog::attributes.slug')),

            IconEntry::make('active')
                ->boolean()
                ->label(__('vendra-blog::attributes.active')),

            TextEntry::make('description')
                ->columnSpanFull()
                ->formatStateUsing(fn (array|string|null $state): string => self::renderRichContent($state))
                ->html()
                ->label(__('vendra-blog::attributes.description')),

            ModelImageEntry::make()
                ->collection(BlogPost::MEDIA_COLLECTION),

            self::dateEntry('created_at'),
            self::dateEntry('updated_at'),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsEntry::make()
                ->type(BlogPost::TAG_TYPE);
        }

        return $schema
            ->components($components)
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
