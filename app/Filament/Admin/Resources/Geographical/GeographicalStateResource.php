<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Geographical;

use App\Filament\Admin\Resources\Geographical\GeographicalStateResource\Pages;
use App\Models\Geographical\GeographicalState;
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

final class GeographicalStateResource extends Resource
{
    protected static ?string $model = GeographicalState::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'geographicals/states';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('geographical_zone_id')
                    ->afterStateUpdated(function (Set $set): void {
                        $set('geographical_country_id', null);
                        $set('geographical_state_id', null);
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
                    ->native(false)
                    ->preload()
                    ->relationship('geographicalCountry', 'name', function (Builder $query, Get $get): void {
                        $query->when($get('geographical_zone_id') ?? false, fn() => $query->where('geographical_zone_id', $get('geographical_zone_id')));
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
                    ->collection('geographicals/states')
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
        return __('navigation.geographical_state');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.geographical_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.geographical_state');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGeographicalState::route('/'),
            'create' => Pages\CreateGeographicalState::route('/create'),
            'view'   => Pages\ViewGeographicalState::route('/{record}'),
            'edit'   => Pages\EditGeographicalState::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.geographical_state');
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
                    ->label(__('form.status'))
                    ->onIcon('heroicon-m-bolt'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('form.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('form.updated_at'))
                    ->dateTime('Y-m-d H:i')
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
            ])
            ->defaultGroup('geographicalCountry.name')
            ->defaultSort('id', 'desc');
    }
}
