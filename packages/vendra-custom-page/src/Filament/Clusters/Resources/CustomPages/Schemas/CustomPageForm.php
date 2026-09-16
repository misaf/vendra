<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraSupport\Filament\Forms\Components\DescriptionRichEditor;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;

final class CustomPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('custom_page_category_id')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.custom_page_category_id'))
                    ->columnSpanFull()
                    ->label(__('vendra-custom-page::navigation.custom_page_category'))
                    ->live()
                    ->native(false)
                    ->preload()
                    ->relationship('customPageCategory', 'name')
                    ->required()
                    ->searchable(),

                SluggableNameInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                SlugInput::make()
                    ->uniqueWithinTenant(perLocale: true),

                DescriptionRichEditor::make(),

                ModelImageUpload::make()
                    ->collection(CustomPage::MEDIA_COLLECTION),

                IsActiveToggle::make()
                    ->default(false),
            ]);
    }
}
