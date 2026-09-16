<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;

final class BlogPostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('blogPostCategory.name')
                ->label(__('vendra-blog::navigation.blog_post_category')),

            NameEntry::make(),

            SlugEntry::make(),

            IsActiveEntry::make(),

            DescriptionEntry::make()
                ->richContent(),

            ModelImageEntry::make()
                ->collection(BlogPost::MEDIA_COLLECTION),

            CreatedAtEntry::make(),
            UpdatedAtEntry::make(),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsEntry::make()
                ->type(BlogPost::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }
}
