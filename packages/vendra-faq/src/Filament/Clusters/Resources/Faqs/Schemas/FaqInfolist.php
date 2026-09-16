<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraTagger\Filament\Infolists\Components\ModelTagsEntry;

final class FaqInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            TextEntry::make('faqCategory.name')
                ->label(__('vendra-faq::navigation.faq_category')),
            NameEntry::make(),
            SlugEntry::make(),
            IsActiveEntry::make(),
            DescriptionEntry::make()
                ->richContent(),
            ModelImageEntry::make()
                ->collection(Faq::MEDIA_COLLECTION),
            CreatedAtEntry::make(),
            UpdatedAtEntry::make(),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsEntry::make()
                ->type(Faq::TAG_TYPE);
        }

        return $schema
            ->components($components)
            ->columns(2);
    }
}
