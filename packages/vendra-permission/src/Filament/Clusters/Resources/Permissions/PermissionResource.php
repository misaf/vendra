<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Permissions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\CreatePermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\EditPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ListPermissions;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Pages\ViewPermission;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\PermissionForm;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Schemas\PermissionInfolist;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\Tables\PermissionTable;
use Misaf\VendraPermission\Models\Permission;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = NavigationPriority::Permissions->value;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'permissions';

    protected static ?string $cluster = CustomersCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-permission::navigation.permission');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-permission::navigation.permission');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-permission::navigation.permissions');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-permission::navigation.permissions');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'guard_name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'view' => ViewPermission::route('/{record}'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PermissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $tenant = resolve(TenantResolver::class)->current();

        return Feature::for($tenant)->active(PermissionFeatureEnum::ModuleEnabled->value)
            && Feature::for($tenant)->active(PermissionFeatureEnum::PermissionManagement->value);
    }
}
