<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Filament\Clusters\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Livewire\Component as Livewire;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraMultimedia\Filament\Forms\Components\ModelImageUpload;
use Misaf\VendraSupport\Capabilities\TagIntegration;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Forms\Components\SluggableNameInput;
use Misaf\VendraSupport\Filament\Forms\Components\SlugInput;
use Misaf\VendraTagger\Filament\Forms\Components\ModelTagsInput;

final class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = [
            Select::make('blog_post_category_id')
                ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.blog_post_category_id'))
                ->columnSpanFull()
                ->label(__('vendra-blog::navigation.blog_post_category'))
                ->live()
                ->native(false)
                ->preload()
                ->relationship('blogPostCategory', 'name')
                ->required()
                ->searchable(),

            SluggableNameInput::make()
                ->uniqueWithinTenant(perLocale: true),

            SlugInput::make()
                ->uniqueWithinTenant(perLocale: true),

            RichEditor::make('description')
                ->columnSpanFull()
                ->label(__('vendra-blog::attributes.description'))
                ->required()
                ->json(),

            ModelImageUpload::make()
                ->collection(BlogPost::MEDIA_COLLECTION)
                ->multiple(),

            IsActiveToggle::make()
                ->default(false),
        ];

        if (TagIntegration::isAvailable()) {
            $components[] = ModelTagsInput::make()
                ->type(BlogPost::TAG_TYPE);
        }

        return $schema
            ->components($components);
    }
}
