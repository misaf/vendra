<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Tenancy\Database\Seeders\PermissionPolicySeeder;

function supportTestPermissionPolicySeeder(): PermissionPolicySeeder
{
    return new class extends PermissionPolicySeeder
    {
        /**
         * @return list<string>
         */
        protected function policies(): array
        {
            return ['view-any-support-test-record', 'view-support-test-record'];
        }
    };
}

/**
 * @return class-string<Model>
 */
function supportTestPermissionModel(): string
{
    /** @var class-string<Model> */
    return Config::string('permission.models.permission');
}

it('seeds its policies again without duplicating existing permissions', function (): void {
    makeCurrentTestTenant();

    supportTestPermissionPolicySeeder()->run();
    supportTestPermissionPolicySeeder()->run();

    expect(supportTestPermissionModel()::query()->where('name', 'like', '%support-test-record')->count())->toBe(2);
});

it('seeds the same policies for each tenant', function (): void {
    $firstTenant = makeCurrentTestTenant();
    supportTestPermissionPolicySeeder()->run();

    $secondTenant = createTestTenant();
    switchToTestTenant($secondTenant);
    supportTestPermissionPolicySeeder()->run();

    expect(supportTestPermissionModel()::query()->withoutGlobalScopes()->where('name', 'view-support-test-record')->count())->toBe(2);

    switchToTestTenant($firstTenant);
});
