<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Console\Commands;

use Misaf\VendraCart\CartPlugin;
use Misaf\VendraCart\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCart\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = CartPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed cart data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed cart module data for a tenant';

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
