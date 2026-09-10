<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Clusters\Resources\Orders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use InvalidArgumentException;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Pages\ListOrders;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Pages\ViewOrder;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\RelationManagers\OrderLinesRelationManager;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Schemas\OrderForm;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Schemas\OrderInfolist;
use Misaf\VendraOrder\Filament\Clusters\Resources\Orders\Tables\OrderTable;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = NavigationPriority::Orders->value;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $slug = 'orders';

    protected static ?string $cluster = SalesCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-order::navigation.order');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-order::navigation.order');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-order::navigation.orders');
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(Order::query()->pending()->count());
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-order::navigation.navigation_badge_tooltip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-order::navigation.orders');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'payment_reference'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('customer');
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $order = self::order($record);

        return [
            __('vendra-order::attributes.customer') => $order->customer_label ?? '—',
            __('vendra-order::attributes.status') => $order->status->getLabel(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrderLinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    private static function order(Model $record): Order
    {
        throw_unless($record instanceof Order, InvalidArgumentException::class, 'Order resources require an Order record.');

        return $record;
    }
}
