<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\CreateDeliveryZone;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\EditDeliveryZone;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\ListDeliveryZones;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Pages\ViewDeliveryZone;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Schemas\DeliveryZoneForm;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Schemas\DeliveryZoneInfolist;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliveryZones\Tables\DeliveryZoneTable;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class DeliveryZoneResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = DeliveryZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = NavigationPriority::DeliveryZones->value;

    protected static ?string $slug = 'delivery-zones';

    protected static ?string $cluster = SalesCluster::class;

    /**
     * @return array<int, string>
     */
    protected static function translatableGlobalSearchAttributes(): array
    {
        return ['name'];
    }

    public static function getBreadcrumb(): string
    {
        return __('vendra-delivery::navigation.delivery_zone');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_zone');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_zones');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_zones');
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return DeliveryZoneForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeliveryZoneInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryZoneTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryZones::route('/'),
            'create' => CreateDeliveryZone::route('/create'),
            'view' => ViewDeliveryZone::route('/{record}'),
            'edit' => EditDeliveryZone::route('/{record}/edit'),
        ];
    }
}
