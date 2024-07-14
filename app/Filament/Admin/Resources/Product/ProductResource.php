<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Product;

use App\Filament\Admin\Actions\Tables\Product\InStockAction;
use App\Filament\Admin\Actions\Tables\Product\OutOfStockAction;
use App\Filament\Admin\Resources\Product\ProductResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Termehsoft\Product\Models\Product;
use Termehsoft\Product\Models\ProductCategory;
use Termehsoft\Product\Models\ProductPrice;

final class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'products/products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->columns(2)
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('general')
                            ->label(__('tab.general'))
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Select::make('product_category_id')
                                    ->options([
                                        'web' => [
                                            'ios_mobile' => 'iOS development',
                                        ],
                                        'mobile' => [
                                            'ios_mobile'     => 'iOS development',
                                            'android_mobile' => 'Android development',
                                        ],
                                        'design' => [
                                            'app_design'               => 'Panel design',
                                            'marketing_website_design' => 'Marketing website design',
                                        ],
                                    ]),

                                Forms\Components\TextInput::make('name')
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                        if (($get('slug') ?? '') !== Str::slug($old)) {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    })
                                    ->autofocus()
                                    ->columnSpanFull()
                                    ->label(__('form.name'))
                                    ->live(onBlur: true)
                                    ->required()
                                    ->unique(
                                        column: fn($livewire) => 'name->' . $livewire->activeLocale,
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn(Unique $rule) => $rule->where('tenant_id', app('currentTenant')->id)->whereNull('deleted_at'),
                                    )
                                    ->translatable(),

                                Forms\Components\RichEditor::make('description')
                                    ->columnSpanFull()
                                    ->label(__('form.description'))
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'underline',
                                        'undo',
                                    ])
                                    ->translatable(),

                                Forms\Components\Group::make()
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->relationship(
                                        name:'latestProductPrice',
                                        condition: fn(?array $state): bool => filled($state['price']),
                                    )
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->autocomplete(false)
                                            ->columnSpanFull()
                                            ->extraInputAttributes(['dir' => 'ltr'])
                                            ->formatStateUsing(fn(?ProductPrice $record) => $record?->price->getAmount()->__toString())
                                            ->label(__('form.price'))
                                            ->numeric(),
                                    ]),

                                Forms\Components\TextInput::make('quantity')
                                    ->autocomplete(false)
                                    ->columnSpan([
                                        'lg' => 1,
                                    ])
                                    ->extraInputAttributes(['dir' => 'ltr'])
                                    ->label(__('form.quantity'))
                                    ->numeric(),

                                Forms\Components\TextInput::make('stock_threshold')
                                    ->autocomplete(false)
                                    ->columnSpan([
                                        'lg' => 1,
                                    ])
                                    ->extraInputAttributes(['dir' => 'ltr'])
                                    ->label(__('form.stock_threshold'))
                                    ->numeric(),

                                Forms\Components\DateTimePicker::make('availability_date')
                                    ->closeOnDateSelection()
                                    ->columnSpanFull()
                                    ->displayFormat('Y-m-d H:i:s')
                                    ->extraAttributes(['dir' => 'ltr'])
                                    ->firstDayOfWeek(6)
                                    ->jalali()
                                    ->label(__('form.availability_date'))
                                    ->minDate(now())
                                    ->native(false),

                                Forms\Components\Toggle::make('in_stock')
                                    ->columnSpanFull()
                                    ->inline()
                                    ->label(__('form.in_stock')),

                                Forms\Components\Toggle::make('available_soon')
                                    ->columnSpanFull()
                                    ->inline()
                                    ->label(__('form.available_soon'))
                                    ->required(),
                            ]),

                        Forms\Components\Tabs\Tab::make('image')
                            ->icon('heroicon-o-photo')
                            ->label(__('tab.image'))
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                                    ->columnSpanFull()
                                    ->image()
                                    ->imageEditor()
                                    ->label(__('form.image'))
                                    ->multiple()
                                    ->reorderable()
                                    ->responsiveImages(),
                            ]),

                        Forms\Components\Tabs\Tab::make('seo')
                            ->icon('heroicon-o-rocket-launch')
                            ->label(__('tab.seo'))
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->label(__('form.slug')),

                                // Forms\Components\SpatieTagsInput::make('tags')
                                //     ->columnSpanFull()
                                //     ->label(__('form.tag'))
                                //     ->nestedRecursiveRules([
                                //         'min:3',
                                //         'max:20',
                                //     ])
                                //     ->reorderable()
                                //     ->splitKeys(['Tab', ' '])
                                //     ->translatable()
                            ]),
                    ])
                    ->id('product-tabs')
                    ->persistTab()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function getBreadcrumb(): string
    {
        return __('navigation.product_management');
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function getModelLabel(): string
    {
        return __('navigation.product');
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::rememberForever('product_row_count', fn() => (string) Number::format(static::getModel()::count()));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.product_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.product');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProduct::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getpluralModelLabel(): string
    {
        return __('navigation.product');
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
                    ->limit(3)
                    ->limitedRemainingText()
                    ->limitedRemainingText(isSeparate: true, size: 'xs')
                    ->stacked(),
                // ->responsiveImages()

                Tables\Columns\TextColumn::make('name')
                    ->label(__('form.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('token')
                    ->copyable()
                    ->copyMessage(__('Token copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->label(__('form.token'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_threshold')
                    ->badge()
                    ->formatStateUsing(fn(int $state) => number_format($state))
                    ->label(__('form.stock_threshold')),

                Tables\Columns\TextColumn::make('quantity')
                    ->badge()
                    ->formatStateUsing(fn(int $state) => number_format($state))
                    ->label(__('form.quantity')),

                Tables\Columns\TextColumn::make('latestProductPrice.price')
                    ->alignCenter()
                    ->copyable()
                    ->copyableState(fn($state) => $state->getAmount())
                    ->copyMessage(__('Price copied to clipboard'))
                    ->copyMessageDuration(1500)
                    ->extraCellAttributes(['dir' => 'ltr'])
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(fn(Product $record) => $record->latestProductPrice->getFormattedPrice())
                    ->label(__('form.price'))
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): void {
                        $query->withAggregate('latestProductPrice', 'price')->orderBy("latest_product_price_price", $direction);
                    }),

                Tables\Columns\ToggleColumn::make('in_stock')
                    ->label(__('form.in_stock'))
                    ->onIcon('heroicon-m-bolt'),

                Tables\Columns\ToggleColumn::make('available_soon')
                    ->label(__('form.available_soon'))
                    ->onIcon('heroicon-m-bolt'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->label(__('form.created_at'))
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('productCategory')
                    ->getOptionLabelFromRecordUsing(fn(ProductCategory $record, $livewire) => $record->getTranslation('name', $livewire->activeLocale))
                    ->label(__('model.product_category'))
                    ->preload()
                    ->relationship(
                        name: 'productCategory',
                        titleAttribute: 'name',
                    )
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    InStockAction::make(),
                    OutOfStockAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('productCategory.name')
                    ->collapsible()
                    ->label(__('model.product_category')),
            ])
            ->defaultSort('position', 'desc')
            ->paginatedWhileReordering()
            ->reorderable('position');
        // ->poll()
    }
}
