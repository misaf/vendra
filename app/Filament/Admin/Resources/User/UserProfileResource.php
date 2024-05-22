<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User;

use App\Filament\Admin\Resources\User\UserProfileDocumentResource\RelationManagers\UserProfileDocumentRelationManager;
use App\Filament\Admin\Resources\User\UserProfilePhoneResource\RelationManagers\UserProfilePhoneRelationManager;
use App\Filament\Admin\Resources\User\UserProfileResource\Pages;
use App\Models\User\UserProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\GlobalSearch\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

final class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'users/profiles/profiles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->columnSpanFull()
                    ->helperText(str(__('Only existing users will be displayed here.'))->inlineMarkdown()->toHtmlString())
                    ->label(__('model.user'))
                    ->native(false)
                    ->preload()
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('tenant_id', app('currentTenant')->id)->whereNull('deleted_at'),
                    ),

                Forms\Components\TextInput::make('first_name')
                    ->autofocus()
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.first_name'))
                    ->required(),

                Forms\Components\TextInput::make('last_name')
                    ->autofocus()
                    ->columnSpan([
                        'lg' => 1,
                    ])
                    ->label(__('form.last_name'))
                    ->required(),

                Forms\Components\DatePicker::make('birthdate')
                    ->closeOnDateSelection()
                    ->columnSpanFull()
                    ->displayFormat('Y-m-d')
                    ->firstDayOfWeek(6)
                    ->helperText(str(__('Only individuals aged 7 years and older are eligible to create a profile.'))->inlineMarkdown()->toHtmlString())
                    ->label(__('form.birthdate'))
                    ->maxDate(today()->subYears(7))
                    ->native(false)
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label(__('form.description'))
                    ->rows(5),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->avatar()
                    ->circleCropper()
                    ->imageEditor()
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
        return __('navigation.user_management');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'userProfilePhone']);
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('view')
                ->url(static::getUrl('view', ['record' => $record])),
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('form.email') => str('<span dir="ltr">' . $record->user->email . '</span>')->toHtmlString(),
            __('form.phone') => str('<span dir="ltr">' . $record->userProfilePhone->phone . '</span>')->toHtmlString(),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return str('<span dir="ltr">' . $record->full_name . '</span>')->toHtmlString();
    }

    public static function getModelLabel(): string
    {
        return __('navigation.user_profile');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.user_profile');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserProfile::route('/'),
            'create' => Pages\CreateUserProfile::route('/create'),
            'view'   => Pages\ViewUserProfile::route('/{record}'),
            'edit'   => Pages\EditUserProfile::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.user_profile');
    }

    public static function getRelations(): array
    {
        return [
            UserProfileDocumentRelationManager::class,
            UserProfilePhoneRelationManager::class,
        ];
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

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('model.user'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('form.first_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('form.last_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('birthdate')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDate()
                    ->label(__('form.birthdate'))
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
