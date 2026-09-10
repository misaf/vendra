<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Console\Commands;

use Misaf\VendraAttribute\AttributePlugin;
use Misaf\VendraAttribute\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = AttributePlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed attribute data for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}';

    protected $description = 'Seed attribute module data for a tenant';

    /** @return array<string, class-string> */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
