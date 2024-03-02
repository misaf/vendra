<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User;

use App\Filament\Admin\Resources\User\UserProfileDocumentResource\RelationManagers\UserProfileDocumentRelationManager;
use App\Filament\Admin\Resources\User\UserProfileResource\RelationManagers\UserProfileRelationManager;
use App\Filament\Admin\Resources\User\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Validation\Rules\Unique;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'users/users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('username')
                //     ->autocomplete(false)
                //     ->autofocus()
                //     ->label(__('form.username'))
                //     ->maxLength(255),
                // ->required()

                Forms\Components\TextInput::make('email')
                    ->autocomplete(false)
                    ->email()
                    ->extraAttributes(['dir' => 'ltr'])
                    ->label(__('form.email'))
                    ->maxLength(255)
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule): void {
                        $rule->whereNull('deleted_at');
                    }),

                Forms\Components\DatePicker::make('email_verified_at')
                    ->closeOnDateSelection()
                    ->displayFormat('d/m/Y')
                    ->firstDayOfWeek(6)
                    ->label(__('form.email_verified_at'))
                    ->maxDate(now())
                    ->native(false),

                Forms\Components\TextInput::make('password')
                    ->autocomplete(false)
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->label(__('form.password'))
                    ->password()
                    ->required(fn(string $operation): bool => 'create' === $operation)
                    ->revealable(),


                Forms\Components\Select::make('role')
                    ->label(__('model.role'))
                    ->multiple()
                    ->native(false)
                    ->preload()
                    ->relationship('roles', 'name')
                    ->searchable(),

                Forms\Components\Select::make('permission')
                    ->label(__('model.permission'))
                    ->multiple()
                    ->native(false)
                    ->preload()
                    ->relationship('permissions', 'name')
                    ->searchable(),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.user_management');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email'   => $record->email,
            'Roles'   => Arr::join($record->roles()->pluck('name')->toArray(), ', '),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('model.user');
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::rememberForever('user_row_count', fn() => (string) Number::format(static::getModel()::count()));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.user');
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         // UserProfileRelationManager::class,
    //         // UserProfileDocumentRelationManager::class
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.user');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('userProfile.image')
                    ->circular()
                    ->conversion('thumb-table')
                    ->extraImgAttributes(['class' => 'saturate-50', 'loading' => 'lazy'])
                    ->label(__('form.image'))
                    ->stacked()
                    ->defaultImageUrl(url('coin-payment/images/default.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.username'))
                    ->sortable()
                    ->searchable(isGlobal: true),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('form.email'))
                    ->sortable()
                    ->searchable()
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr']),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->badge()
                    ->jalaliDate('Y-m-d H:i')
                    ->label(__('form.email_verified_at'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDateTime('Y-m-d H:i')
                    ->label(__('form.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDateTime('Y-m-d H:i')
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
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
