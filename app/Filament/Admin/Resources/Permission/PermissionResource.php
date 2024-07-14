<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Permission;

use App\Filament\Admin\Resources\Permission\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Termehsoft\Permission\Models\Permission;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'users/permissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        if (($get('guard_name') ?? '') !== Str::slug($old)) {
                            return;
                        }

                        $set('guard_name', Str::slug($state));
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

                Forms\Components\TextInput::make('guard_name')
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.guard_name'))
                    ->required(),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.setting_management');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.permission');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.setting_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.permission');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPermission::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'view'   => Pages\ViewPermission::route('/{record}'),
            'edit'   => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.permission');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label(__('form.guard_name'))
                    ->searchable()
                    ->sortable()
                    ->badge(),

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
            ->defaultSort('id', 'desc');
    }
}
