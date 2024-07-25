<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User;

use App\Filament\Admin\Resources\User\UserProfilePhoneResource\Pages;
use App\Support\Enums\UserProfilePhoneStatusEnum;
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
use Termehsoft\User\Models\UserProfilePhone;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneInputColumn;

final class UserProfilePhoneResource extends Resource
{
    protected static ?string $model = UserProfilePhone::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'users/profiles/phones';

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

                PhoneInput::make('phone')
                    ->columnSpanFull()
                    ->countryStatePath('country')
                    ->disallowDropdown()
                    ->displayNumberFormat(PhoneInputNumberType::E164)
                    ->label(__('form.phone'))
                    ->required()
                    ->rule('phone'),

                Forms\Components\ToggleButtons::make('status')
                    ->columnSpanFull()
                    ->inline()
                    ->label(__('form.status'))
                    ->options(UserProfilePhoneStatusEnum::class)
                    ->required(),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.user_management');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['phone', 'phone_normalized', 'phone_national', 'phone_e164'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['userProfile']);
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
            __('Name') => $record->userProfile->full_name,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return str('<span dir="ltr">' . $record->phone . '</span>')->toHtmlString();
    }

    public static function getModelLabel(): string
    {
        return __('navigation.user_profile_phone');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.user_profile_phone');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserProfilePhone::route('/'),
            'create' => Pages\CreateUserProfilePhone::route('/create'),
            'view'   => Pages\ViewUserProfilePhone::route('/{record}'),
            'edit'   => Pages\EditUserProfilePhone::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.user_profile_phone');
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

                PhoneInputColumn::make('phone')
                    ->copyable()
                    ->copyMessage(__('Phone copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->displayFormat(PhoneInputNumberType::INTERNATIONAL)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->label(__('form.phone'))
                    ->searchable(isGlobal: true)
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->afterStateUpdated(fn($record, $state) => $record->setStatus($state))
                    ->beforeStateUpdated(fn($record, $state) => $record->verified_at = $state === UserProfilePhoneStatusEnum::Approved->value ? now() : null)
                    ->label(__('form.status'))
                    ->options(UserProfilePhoneStatusEnum::class),

                Tables\Columns\TextColumn::make('verified_at')
                    ->alignCenter()
                    ->badge()
                    ->extraCellAttributes(['dir' => 'ltr'])
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
                    ->getDescriptionFromRecordUsing(fn(UserProfilePhone $record): string => $record->status->getDescription())
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
