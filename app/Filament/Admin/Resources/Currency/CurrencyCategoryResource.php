<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Currency;

use App\Filament\Admin\Resources\Currency\CurrencyCategoryResource\Pages;
use App\Models\Currency\CurrencyCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

final class CurrencyCategoryResource extends Resource
{
    protected static ?string $model = CurrencyCategory::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'currencys/categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('form.description'))
                    ->rows(5),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->collection('currencies/categories')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('form.image')),

                Forms\Components\Toggle::make('status')
                    ->columnSpanFull()
                    ->default(true)
                    ->label(__('form.status'))
                    ->rules('required'),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.currency_management');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.currency_category');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.currency_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.currency_category');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCurrencyCategory::route('/'),
            'create' => Pages\CreateCurrencyCategory::route('/create'),
            'view'   => Pages\ViewCurrencyCategory::route('/{record}'),
            'edit'   => Pages\EditCurrencyCategory::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.currency_category');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->circular()
                    ->conversion('thumb-table')
                    ->defaultImageUrl(url('coin-payment/images/default.png'))
                    ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                    ->label(__('form.image'))
                    ->stacked(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable()
                    ->sortable(),

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
            ->defaultSort('id', 'desc');
    }
}
