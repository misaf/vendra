<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\CreateCurrency;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\EditCurrency;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\ListCurrencies;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\ViewCurrency;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Schemas\CurrencyForm;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Schemas\CurrencyInfolist;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Tables\CurrencyTable;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = NavigationPriority::Currencies->value;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'currencies';

    protected static ?string $cluster = SalesCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-currency::navigation.currency');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-currency::navigation.currency');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-currency::navigation.currencies');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-currency::navigation.currencies');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name', 'symbol'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'view' => ViewCurrency::route('/{record}'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CurrencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrencyTable::configure($table);
    }
}
