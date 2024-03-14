<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Language;

use App\Filament\Admin\Resources\Language\LanguageLineResource\Pages;
use App\Models\Language\LanguageLine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

final class LanguageLineResource extends Resource
{
    use Translatable;

    protected static ?string $model = LanguageLine::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'languages/translates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group')
                    ->autofocus()
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.group'))
                    ->required(),

                Forms\Components\TextInput::make('key')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.key'))
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('group', $get('group'))),

                Forms\Components\TextInput::make('text')
                    ->columnSpanFull()
                    ->label(__('form.text'))
                    ->required()
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.setting_management');
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function getModelLabel(): string
    {
        return __('navigation.language_line');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.setting_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.language_line');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLanguageLine::route('/'),
            'create' => Pages\CreateLanguageLine::route('/create'),
            'view'   => Pages\ViewLanguageLine::route('/{record}'),
            'edit'   => Pages\EditLanguageLine::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.language_line');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label(__('form.group'))
                    ->searchable(isGlobal:false, isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label(__('form.key'))
                    ->searchable(isGlobal:false, isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('text')
                    ->label(__('form.text'))
                    ->sortable(),

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
            ->defaultSort('id', 'desc')
            ->poll();
    }
}
