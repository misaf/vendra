<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Geographical;

use App\Filament\Admin\Resources\Geographical\GeographicalNeighborhoodResource\Pages;
use App\Models\Geographical\GeographicalNeighborhood;
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

final class GeographicalNeighborhoodResource extends Resource
{
    protected static ?string $model = GeographicalNeighborhood::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'geographicals/neighborhoods';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('geographical_zone_id')
                    ->afterStateUpdated(function (Set $set): void {
                        $set('geographical_country_id', null);
                        $set('geographical_state_id', null);
                        $set('geographical_city_id', null);
                    })
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('model.geographical_zone'))
                    ->live()
                    ->native(false)
                    ->preload()
                    ->relationship('geographicalZone', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('geographical_country_id')
                    ->afterStateUpdated(function (Set $set): void {
                        $set('geographical_state_id', null);
                    })
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('model.geographical_country'))
                    ->live()
                    ->native(false)
                    ->preload()
                    ->relationship('geographicalCountry', 'name', function (Builder $query, Get $get): void {
                        $query->when($get('geographical_zone_id') ?? false, fn() => $query->where('geographical_zone_id', $get('geographical_zone_id')));
                    })
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('geographical_state_id')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('model.geographical_state'))
                    ->live()
                    ->native(false)
                    ->preload()
                    ->relationship('geographicalState', 'name', function (Builder $query, Get $get): void {
                        $query->when($get('geographical_country_id') ?? false, fn() => $query->where('geographical_country_id', $get('geographical_country_id')));
                    })
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('geographical_city_id')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('model.geographical_city'))
                    ->native(false)
                    ->preload()
                    ->relationship('geographicalCity', 'name', function (Builder $query, Get $get): void {
                        $query->when($get('geographical_state_id') ?? false, fn() => $query->where('geographical_state_id', $get('geographical_state_id')));
                    })
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
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn(Unique $rule) => $rule->whereNull('deleted_at')),

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
                    ->collection('geographicals/neighborhoods')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('form.image')),

                Forms\Components\Toggle::make('status')
                    ->columnSpanFull()
                    ->label(__('form.status'))
                    ->rules('required')
                    ->default(true),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.geographical_management');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.geographical_neighborhood');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.geographical_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.geographical_neighborhood');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGeographicalNeighborhoods::route('/'),
            'create' => Pages\CreateGeographicalNeighborhood::route('/create'),
            'view'   => Pages\ViewGeographicalNeighborhood::route('/{record}'),
            'edit'   => Pages\EditGeographicalNeighborhood::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.geographical_neighborhood');
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

                Tables\Columns\ToggleColumn::make('status')
                    ->label(__('form.status')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('form.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('form.updated_at'))
                    ->dateTime()
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
                Tables\Grouping\Group::make('geographicalZone.name')
                    ->collapsible()
                    ->label(__('model.geographical_zone')),

                Tables\Grouping\Group::make('geographicalCountry.name')
                    ->collapsible()
                    ->label(__('model.geographical_country')),
                    
                Tables\Grouping\Group::make('geographicalState.name')
                    ->collapsible()
                    ->label(__('model.geographical_state')),
                
                Tables\Grouping\Group::make('geographicalCity.name')
                    ->collapsible()
                    ->label(__('model.geographical_city')),
            ])
            ->defaultGroup('geographicalCity.name')
            ->defaultSort('id', 'desc');
    }
}
