<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Currencies\Resources\CurrencyCategories\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\Tenant\Models\Tenant;

final class CurrencyCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get('slug') ?? '') === Str::slug($old ?? '')) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->autofocus()
                    ->columnSpan(['lg' => 1])
                    ->label(__('currency::attributes.name'))
                    ->live(onBlur: true)
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule): void {
                            $rule->where('tenant_id', Tenant::current()->id)
                                ->withoutTrashed();
                        },
                    ),

                TextInput::make('slug')
                    ->afterStateUpdated(function (Livewire $livewire): void {
                        $livewire->validateOnly("data.slug");
                    })
                    ->columnSpan(['lg' => 1])
                    ->helperText(__('currency::attributes.slug_helper_text'))
                    ->label(__('currency::attributes.slug'))
                    ->required()
                    ->unique(
                        modifyRuleUsing: function (Unique $rule): void {
                            $rule->withoutTrashed();
                        },
                    )
                    ->label(__('currency::attributes.slug')),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('currency::attributes.description'))
                    ->required()
                    ->rows(5),

                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('currencies/categories')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('currency::attributes.image'))
                    ->panelLayout('grid'),

                Toggle::make('status')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.status"))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('currency::attributes.status'))
                    ->onIcon('heroicon-m-bolt')
                    ->required()
                    ->rules([
                        'boolean',
                    ]),
            ]);
    }
}
