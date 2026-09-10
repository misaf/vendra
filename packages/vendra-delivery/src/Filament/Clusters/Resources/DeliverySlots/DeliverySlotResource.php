<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\CreateDeliverySlot;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\EditDeliverySlot;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\ListDeliverySlots;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Pages\ViewDeliverySlot;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Schemas\DeliverySlotForm;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Schemas\DeliverySlotInfolist;
use Misaf\VendraDelivery\Filament\Clusters\Resources\DeliverySlots\Tables\DeliverySlotTable;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraSupport\Filament\Clusters\SalesCluster;
use Misaf\VendraSupport\Filament\Concerns\InteractsWithTranslatedGlobalSearch;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class DeliverySlotResource extends Resource
{
    use InteractsWithTranslatedGlobalSearch;
    use Translatable;

    protected static ?string $model = DeliverySlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = NavigationPriority::DeliverySlots->value;

    protected static ?string $slug = 'delivery-slots';

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
        return __('vendra-delivery::navigation.delivery_slot');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_slot');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_slots');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_slots');
    }

    public static function getDefaultTranslatableLocale(): string
    {
        return app()->getLocale();
    }

    public static function form(Schema $schema): Schema
    {
        return DeliverySlotForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeliverySlotInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverySlotTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliverySlots::route('/'),
            'create' => CreateDeliverySlot::route('/create'),
            'view' => ViewDeliverySlot::route('/{record}'),
            'edit' => EditDeliverySlot::route('/{record}/edit'),
        ];
    }
}
