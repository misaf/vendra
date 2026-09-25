<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraDelivery\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraDelivery\DeliveryPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed delivery module data for a tenant')]
#[Signature(self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed delivery data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = DeliveryPlugin::ID;

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
