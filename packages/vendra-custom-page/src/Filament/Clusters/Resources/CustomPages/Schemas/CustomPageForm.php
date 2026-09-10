<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage\Filament\Clusters\Resources\CustomPages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedFormFields;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

final class CustomPageForm
{
    use InteractsWithTranslatedFormFields;

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

                TextInput::make('name')
                    ->afterStateUpdated(function (Livewire $livewire, Get $get, Set $set, ?string $old, ?string $state): void {
                        $livewire->validateOnly('data.name');

                        if (($get->string('slug', isNullable: true) ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('vendra-custom-page::attributes.name'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        column: fn (Livewire $livewire): string => 'name->'.self::activeFormLocale($livewire),
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->withoutTrashed(),
                    ),

                TextInput::make('slug')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.slug'))
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('vendra-custom-page::attributes.slug_helper_text'))
                    ->label(__('vendra-custom-page::attributes.slug'))
                    ->live(onBlur: true)
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        column: fn (Livewire $livewire): string => 'slug->'.self::activeFormLocale($livewire),
                        modifyRuleUsing: fn (Unique $rule): Unique => TenantAwareness::constrainUniqueRule($rule)
                            ->withoutTrashed(),
                    ),

                RichEditor::make('description')
                    ->columnSpanFull()
                    ->label(__('vendra-custom-page::attributes.description'))
                    ->required()
                    ->json(),

                SpatieMediaLibraryFileUpload::make('image')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.image'))
                    ->collection(CustomPage::MEDIA_COLLECTION)
                    ->columnSpanFull()
                    ->image()
                    ->label(__('vendra-custom-page::attributes.image'))
                    ->live()
                    ->panelLayout('grid')
                    ->responsiveImages(),

                Toggle::make('active')
                    ->afterStateUpdated(fn (Livewire $livewire) => $livewire->validateOnly('data.active'))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('vendra-custom-page::attributes.active'))
                    ->live()
                    ->onIcon(Heroicon::Bolt)
                    ->required()
                    ->rules([
                        'boolean',
                    ]),
            ]);
    }
}
