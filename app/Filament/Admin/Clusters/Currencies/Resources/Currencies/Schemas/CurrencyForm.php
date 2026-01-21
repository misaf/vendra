<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Currencies\Resources\Currencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Misaf\Tenant\Models\Tenant;

final class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_category_id')
                    ->columnSpanFull()
                    ->label(__('currency::navigation.currency_category'))
                    ->native(false)
                    ->preload()
                    ->relationship('currencyCategory', 'name')
                    ->required()
                    ->searchable(),

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

                Fieldset::make('currency_setting')
                    ->columns(3)
                    ->label(__('currency::attributes.currency_setting'))
                    ->schema([
                        TextInput::make('iso_code')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->label(__('currency::attributes.iso_code'))
                            ->required(),

                        TextInput::make('conversion_rate')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            // ->inputMode('decimal')
                            ->label(__('currency::attributes.conversion_rate'))
                            ->required(),

                        TextInput::make('decimal_place')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->inputMode('decimal')
                            ->label(__('currency::attributes.decimal_place'))
                            ->required(),
                    ]),

                Fieldset::make('exchange_setting')
                    ->columns(2)
                    ->label(__('currency::attributes.exchange_setting'))
                    ->schema([
                        TextInput::make('buy_price')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->label(__('currency::attributes.buy_price'))
                            ->required()
                            ->numeric(),

                        TextInput::make('sell_price')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->label(__('currency::attributes.sell_price'))
                            ->required()
                            ->numeric(),
                    ]),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('currency::attributes.description'))
                    ->required()
                    ->rows(5),

                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('currencies')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('currency::attributes.image'))
                    ->panelLayout('grid'),

                Toggle::make('is_default')
                    ->afterStateUpdated(fn(Livewire $livewire) => $livewire->validateOnly("data.is_default"))
                    ->columnSpanFull()
                    ->default(false)
                    ->label(__('currency::attributes.is_default'))
                    ->onIcon('heroicon-m-bolt')
                    ->required()
                    ->rules([
                        'boolean',
                    ]),

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
