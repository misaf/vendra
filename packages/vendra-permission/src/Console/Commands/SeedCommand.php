<?php

declare(strict_types=1);

namespace Misaf\VendraPermission\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraPermission\Database\Seeders\DemoContentSeeder;
use Misaf\VendraPermission\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraPermission\PermissionPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed permission module data for a tenant')]
#[Signature(self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed permission data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = PermissionPlugin::ID;

    /**
     * @return array<string, class-string>
     */
    public static function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
            'demo-contents' => DemoContentSeeder::class,
        ];
    }
}
