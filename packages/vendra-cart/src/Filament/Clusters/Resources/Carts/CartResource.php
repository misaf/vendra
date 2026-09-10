<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Clusters\Resources\Carts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Pages\ListCarts;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Pages\ViewCart;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\RelationManagers\CartItemsRelationManager;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Schemas\CartForm;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Schemas\CartInfolist;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Tables\CartTable;
use Misaf\VendraCart\Filament\Clusters\Resources\Carts\Widgets\CartOverviewWidget;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = NavigationPriority::Carts->value;

    protected static ?string $recordTitleAttribute = 'token';

    protected static ?string $slug = 'carts';

    protected static ?string $cluster = SalesCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-cart::navigation.cart');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-cart::navigation.cart');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-cart::navigation.carts');
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(Cart::query()->count());
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-cart::navigation.navigation_badge_tooltip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-cart::navigation.carts');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['token'];
    }

    public static function form(Schema $schema): Schema
    {
        return CartForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CartInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CartItemsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CartOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarts::route('/'),
            'view' => ViewCart::route('/{record}'),
        ];
    }
}
