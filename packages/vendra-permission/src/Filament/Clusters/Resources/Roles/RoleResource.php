<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Filament\Clusters\Resources\Roles;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\RelationManagers\PermissionRelationManager;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\CreateRole;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\EditRole;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\ListRoles;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Pages\ViewRole;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Schemas\RoleForm;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Schemas\RoleInfolist;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\Tables\RoleTable;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Filament\Clusters\CustomersCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = NavigationPriority::Roles->value;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'roles';

    protected static ?string $cluster = CustomersCluster::class;

    public static function getBreadcrumb(): string
    {
        return __('vendra-permission::navigation.role');
    }

    public static function getModelLabel(): string
    {
        return __('vendra-permission::navigation.role');
    }

    public static function getNavigationLabel(): string
    {
        return __('vendra-permission::navigation.roles');
    }

    public static function getPluralModelLabel(): string
    {
        return __('vendra-permission::navigation.roles');
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'guard_name'];
    }

    public static function getRelations(): array
    {
        return [
            PermissionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoleTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $tenant = app(TenantResolver::class)->current();

        return Feature::for($tenant)->active(PermissionFeatureEnum::ModuleEnabled->value)
            && Feature::for($tenant)->active(PermissionFeatureEnum::RoleManagement->value);
    }
}
