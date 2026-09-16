<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\FaqCategories\Schemas;

use Filament\Schemas\Schema;
use Misaf\VendraFaq\Models\FaqCategory;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionTextarea;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;

final class FaqCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SluggableNameInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                SlugInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                DescriptionTextarea::make()
                    ->maxLength(65535)
                    ->rows(5),

                ModelImageUpload::make()
                    ->collection(FaqCategory::MEDIA_COLLECTION),

                IsActiveToggle::make()
                    ->default(false),
            ]);
    }
}
