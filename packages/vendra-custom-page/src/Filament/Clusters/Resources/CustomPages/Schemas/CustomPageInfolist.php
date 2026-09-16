<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class CustomPageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customPageCategory.name')
                    ->label(__('vendra-custom-page::navigation.custom_page_category')),
                NameEntry::make(),
                SlugEntry::make(),
                IsActiveEntry::make(),
                DescriptionEntry::make()
                    ->richContent(),
                ModelImageEntry::make()
                    ->collection(CustomPage::MEDIA_COLLECTION),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
