<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages\CreateAttribute;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages\EditAttribute;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages\ListAttributes;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages\ViewAttribute;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\RelationManagers\AttributeValueRelationManager;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas\AttributeForm;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Schemas\AttributeInfolist;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Tables\AttributeTable;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraSupport\Filament\Clusters\CatalogCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = NavigationPriority::Attributes->value;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'attributes';

    protected static ?string $cluster = CatalogCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-attribute::navigation.attribute');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-attribute::navigation.attribute');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-attribute::navigation.attributes');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-attribute::navigation.attributes');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'unit'];
    }

    public static function getRelations(): array
    {
        return [
            AttributeValueRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'view' => ViewAttribute::route('/{record}'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AttributeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttributeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributeTable::configure($table);
    }
}
