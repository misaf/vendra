<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Filament\Clusters\Resources\Faqs\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;
use Misaf\VendraTagger\Filament\Forms\Components\ModelTagsInput;

final class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            Select::make('faq_category_id')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.faq_category_id'))
                ->columnSpanFull()
                ->label(__('vendra-faq::navigation.faq_category'))
                ->live()
                ->native(false)
                ->preload()
                ->relationship('faqCategory', 'name')
                ->required()
                ->searchable(),

            SluggableNameInput::make()
                ->uniqueWithinTenant(perLocale: true),

            SlugInput::make()
                ->uniqueWithinTenant(perLocale: true),

            RichEditor::make('description')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.description'))
                ->columnSpanFull()
                ->label(__('vendra-faq::attributes.description'))
                ->live(onBlur: true)
                ->required()
                ->json(),

            ModelImageUpload::make()
                ->collection(Faq::MEDIA_COLLECTION),

            IsActiveToggle::make()
                ->default(false),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsInput::make()
                ->type(Faq::TAG_TYPE);
        }

        return $schema
            ->components($components);
    }
}
