<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Misaf\VendraDelivery\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraDelivery\DeliveryPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed delivery module data for a tenant')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = DeliveryPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed delivery data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

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
