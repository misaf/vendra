<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User;

use App\Filament\Admin\Resources\User\UserProfileDocumentResource\Pages;
use App\Support\Enums\UserProfileDocumentStatusEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Termehsoft\User\Models\UserProfileDocument;

final class UserProfileDocumentResource extends Resource
{
    protected static ?string $model = UserProfileDocument::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'users/profiles/documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_profile_id')
                    ->columnSpanFull()
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->full_name)
                    ->helperText(str(__('Only existing and valid user profiles will be displayed here.'))->inlineMarkdown()->toHtmlString())
                    ->label(__('model.user_profile'))
                    ->native(false)
                    ->preload()
                    ->relationship(name: 'userProfile', modifyQueryUsing: function (Builder $query): void {
                        $query->whereNotNull('first_name')
                            ->whereNotNull('last_name')
                            ->whereNotNull('birthdate');
                    })
                    ->required()
                    ->searchable()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('tenant_id', app('currentTenant')->id)->whereNull('deleted_at'),
                    ),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->columnSpanFull()
                    ->image()
                    ->label(__('form.image'))
                    ->required(),

                Forms\Components\ToggleButtons::make('status')
                    ->columnSpanFull()
                    ->inline()
                    ->label(__('form.status'))
                    ->options(UserProfileDocumentStatusEnum::class)
                    ->required(),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.user_profile_document');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.user_profile_document');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserProfileDocument::route('/'),
            'create' => Pages\CreateUserProfileDocument::route('/create'),
            'view'   => Pages\ViewUserProfileDocument::route('/{record}'),
            'edit'   => Pages\EditUserProfileDocument::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.user_profile_document');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('latestUserProfile.image')
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

                Tables\Columns\TextColumn::make('userProfile.first_name')
                    ->label(__('form.first_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('userProfile.last_name')
                    ->label(__('form.last_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('userProfile.birthdate')
                    ->badge()
                    ->jalaliDate('Y-m-d')
                    ->label(__('form.birthdate'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label(__('form.status'))
                    ->options(UserProfileDocumentStatusEnum::class)
                    ->beforeStateUpdated(fn($record, $state) => $record->verified_at = $state === UserProfileDocumentStatusEnum::Approved->value ? now() : null)
                    ->afterStateUpdated(fn($record, $state) => $record->setStatus($state)),

                Tables\Columns\TextColumn::make('verified_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->badge()
                    ->jalaliDateTime('Y-m-d H:i')
                    ->label(__('form.verified_at'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->label(__('form.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->alignCenter()
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->jalaliDateTime('Y-m-d H:i')
                    ->label(__('form.updated_at'))
                    ->searchable()
                    ->sortable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->getDescriptionFromRecordUsing(fn(UserProfileDocument $record): string => $record->status->getDescription())
                    ->label(__('form.status')),

                Tables\Grouping\Group::make('verified_at')
                    ->date()
                    ->label(__('form.verified_at')),
            ])
            ->groupingSettingsInDropdownOnDesktop()
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
