<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Console\Commands;

use Misaf\VendraOrder\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraOrder\OrderPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = OrderPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed order data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

    protected $description = 'Seed order module data for a tenant';

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
