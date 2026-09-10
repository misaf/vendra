<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Misaf\VendraCurrency\CurrencyPlugin;
use Misaf\VendraCurrency\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCurrency\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed currency module data for a tenant')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = CurrencyPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed currency data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

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
