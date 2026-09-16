<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPageCategories\Schemas;

use Filament\Schemas\Schema;
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraMultimedia\Filament\Infolists\Components\ModelImageEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DescriptionEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\NameEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\SlugEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;

final class CustomPageCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                NameEntry::make(),
                SlugEntry::make(),
                DescriptionEntry::make(),
                IsActiveEntry::make(),
                ModelImageEntry::make()
                    ->collection(CustomPageCategory::MEDIA_COLLECTION),
                CreatedAtEntry::make(),
                UpdatedAtEntry::make(),
            ])
            ->columns(2);
    }
}
