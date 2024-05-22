<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\User;

use App\Filament\Admin\Resources\User\UserProfileBalanceResource\Pages;
use App\Models\Currency\CurrencyCategory;
use App\Models\User\UserProfileBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

final class UserProfileBalanceResource extends Resource
{
    protected static ?string $model = UserProfileBalance::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'users/profiles/balances';

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
                        // ->whereDoesntHave('userProfileBalances')
                    })
                    ->required()
                    ->searchable(),
                    // ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, Get $get): void {
                    //     $rule->where('currency_id', $get('currency_id'))->whereNull('deleted_at');
                    // })

                Forms\Components\Select::make('currency_id')
                    ->columnSpan(['lg' => 1])
                    ->label(__('model.currency'))
                    ->native(false)
                    ->options(CurrencyCategory::with('currencies')->get()->mapWithKeys(fn($item, $key) => [$item->name => $item->currencies->pluck('name', 'id')]))
                    ->preload()
                    ->required()
                    ->searchable(),
                    // ->unique(
                    //     ignoreRecord: true,
                    //     modifyRuleUsing: fn(Unique $rule) => $rule->where('tenant_id', app('currentTenant')->id)->whereNull('deleted_at'),
                    // ),

                Forms\Components\TextInput::make('amount')
                    ->columnSpan(['lg' => 1])
                    ->extraInputAttributes(['dir' => 'ltr'])
                    ->formatStateUsing(fn($record) => $record?->amount->getAmount()->__toString())
                    ->label(__('form.balance'))
                    ->numeric()
                    ->stripCharacters(','),

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

    public static function getModelLabel(): string
    {
        return __('navigation.user_profile_balance');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.user_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.user_profile_balance');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserProfileBalance::route('/'),
            'create' => Pages\CreateUserProfileBalance::route('/create'),
            'view'   => Pages\ViewUserProfileBalance::route('/{record}'),
            'edit'   => Pages\EditUserProfileBalance::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.user_profile_balance');
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

                Tables\Columns\TextColumn::make('currency.name')
                    ->label(__('model.currency'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->alignCenter()
                    ->copyable()
                    ->copyableState(fn($record) => $record->amount->getAmount())
                    ->copyMessage(__('Phone copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->formatStateUsing(fn($record) => $record->amount->formatTo('en_US'))
                    ->label(__('form.balance'))
                    ->searchable()
                    ->sortable()
                    ->fontFamily(FontFamily::Mono),

                Tables\Columns\ToggleColumn::make('status')
                    ->label(__('form.status'))
                    ->onIcon('heroicon-m-bolt'),

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
