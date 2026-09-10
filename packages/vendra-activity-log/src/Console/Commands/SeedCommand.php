<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Console\Commands;

use Misaf\VendraActivityLog\ActivityLogPlugin;
use Misaf\VendraActivityLog\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = ActivityLogPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed activity log data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

    protected $description = 'Seed activity log module data for a tenant';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
        ];
    }
}
