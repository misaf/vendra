<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Pages\ListDeliveries;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Pages\ViewDelivery;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Schemas\DeliveryForm;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Schemas\DeliveryInfolist;
use Misaf\VendraDelivery\Filament\Clusters\Resources\Deliveries\Tables\DeliveryTable;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = NavigationPriority::Deliveries->value;

    protected static ?string $recordTitleAttribute = 'recipient_name';

    protected static ?string $slug = 'deliveries';

    protected static ?string $cluster = SalesCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-delivery::navigation.delivery');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-delivery::navigation.delivery');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-delivery::navigation.deliveries');
    }

    public static function getNavigationBadge(): string
    {
        return (string) Number::format(
            Delivery::query()->whereDate('scheduled_for', today())->count(),
        );
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return __('vendra-delivery::navigation.navigation_badge_tooltip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-delivery::navigation.deliveries');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['recipient_name'];
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeliveryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveries::route('/'),
            'view' => ViewDelivery::route('/{record}'),
        ];
    }
}
