<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Console\Commands;

use Misaf\VendraPermission\Database\Seeders\DemoContentSeeder;
use Misaf\VendraPermission\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraPermission\PermissionPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = PermissionPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed permission data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed permission module data for a tenant';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
            'demo-contents' => DemoContentSeeder::class,
        ];
    }
}
