<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Currency;

use App\Filament\Admin\Resources\Currency\CurrencyResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Termehsoft\Currency\Models\Currency;

final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'currencies/currencies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('currency_category_id')
                    ->columnSpanFull()
                    ->label(__('model.currency_category'))
                    ->native(false)
                    ->preload()
                    ->relationship('currencyCategory', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get('slug') ?? '') !== Str::slug($old)) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    })
                    ->autofocus()
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.name'))
                    ->live(onBlur: true)
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('tenant_id', app('currentTenant')->id)->whereNull('deleted_at'),
                    ),

                Forms\Components\TextInput::make('slug')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.slug')),

                Forms\Components\Fieldset::make('currency_setting')
                    ->columns(3)
                    ->label(__('form.currency_setting'))
                    ->schema([
                        Forms\Components\TextInput::make('iso_code')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->label(__('form.iso_code'))
                            ->required(),

                        Forms\Components\TextInput::make('conversion_rate')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            // ->inputMode('decimal')
                            ->label(__('form.conversion_rate'))
                            ->required(),

                        Forms\Components\TextInput::make('decimal_place')
                            ->columnSpan([
                                'lg' => 1,
                            ])
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->inputMode('decimal')
                            ->label(__('form.decimal_place'))
                            ->required(),
                    ]),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('form.description'))
                    ->rows(5),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('form.image')),

                Forms\Components\Toggle::make('is_default')
                    ->columnSpanFull()
                    ->label(__('form.is_default'))
                    ->rules('required'),

                Forms\Components\Toggle::make('status')
                    ->columnSpanFull()
                    ->label(__('form.status'))
                    ->rules('required')
                    ->default(true),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.currency_management');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.currency');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.currency_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.currency');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCurrency::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view'   => Pages\ViewCurrency::route('/{record}'),
            'edit'   => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.currency');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->circular()
                    ->conversion('thumb-table')
                    ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                    ->label(__('form.image'))
                    ->stacked()
                    ->defaultImageUrl(url('coin-payment/images/default.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('iso_code')
                    ->badge()
                    ->label(__('form.iso_code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label(__('form.conversion_rate'))
                    ->searchable()
                    ->numeric(fn(Currency $record): int => $record->decimal_place)
                    ->sortable(),

                Tables\Columns\TextColumn::make('decimal_place')
                    ->label(__('form.decimal_place'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_default')
                    ->afterStateUpdated(function (Currency $record, ?string $state): void {
                        Currency::when($state, fn(Builder $query) => $query->whereKeyNot($record->id)->where('is_default', 1))->update(['is_default' => 0]);
                    })
                    ->label(__('form.is_default'))
                    ->onIcon('heroicon-m-bolt'),

                Tables\Columns\ToggleColumn::make('status')
                    ->label(__('form.status'))
                    ->onIcon('heroicon-m-bolt'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->label(__('form.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('Y-m-d H:i')
                    ->label(__('form.updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('currencyCategory.name')
                    ->collapsible()
                    ->label(__('form.category')),
            ])
            ->defaultSort('position', 'desc')
            ->paginatedWhileReordering()
            ->reorderable('position');
        // ->poll()
    }
}
