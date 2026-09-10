<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;
use Misaf\VendraPermission\Enums\PermissionFeatureEnum;
use Misaf\VendraPermission\Filament\Clusters\Resources\Permissions\PermissionResource;
use Misaf\VendraPermission\Filament\Clusters\Resources\Roles\RoleResource;
use Misaf\VendraPermission\Models\Role;
use Misaf\VendraUser\Models\User;
use PHPUnit\Framework\Assert;

final class PermissionModuleTestContext
{
    /**
     * @param  list<string>  $features
     */
    public static function createCurrentTenant(array $features = []): Model
    {
        $tenant = makeCurrentTestTenant();

        if (! $tenant instanceof Model) {
            Assert::fail('Permission module tests require an installed tenant provider.');
        }

        // Every permission feature defaults to active in the real published config, so
        // deactivate whatever wasn't explicitly requested to keep tenant state deterministic.
        $allFeatures = array_map(fn (PermissionFeatureEnum $feature): string => $feature->value, PermissionFeatureEnum::cases());

        Feature::for($tenant)->deactivate(array_values(array_diff($allFeatures, $features)));
        Feature::for($tenant)->activate($features);

        return $tenant;
    }

    public static function setUpFilamentAdminContext(): Model
    {
        $tenant = self::createCurrentTenant([
            PermissionFeatureEnum::ModuleEnabled->value,
            PermissionFeatureEnum::PermissionManagement->value,
            PermissionFeatureEnum::RoleManagement->value,
        ]);

        $adminRole = Role::factory()
            ->forTenant($tenant)
            ->forGuard('web')
            ->create([
                'name' => Config::string('vendra-permission.admin_role', 'admin'),
            ]);

        $user = User::factory()
            ->forTenant($tenant)
            ->create([
                'username' => 'admin',
                'email' => 'admin@example.test',
            ]);

        $user->assignRole($adminRole);

        bootFilamentAdminPanel($user, $tenant, [
            PermissionResource::class,
            RoleResource::class,
        ]);

        return $tenant;
    }
}
