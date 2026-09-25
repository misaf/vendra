<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraAttribute\AttributePlugin;
use Misaf\VendraAttribute\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed attribute module data for a tenant')]
#[Signature(self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed attribute data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = AttributePlugin::ID;

    /**
     * @return array<string, class-string>
     */
    public static function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
        ];
    }
}
