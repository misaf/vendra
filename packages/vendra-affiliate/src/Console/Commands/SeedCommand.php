<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Misaf\VendraAffiliate\AffiliatePlugin;
use Misaf\VendraAffiliate\Database\Seeders\DemoContentSeeder;
use Misaf\VendraAffiliate\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed affiliate module data for a tenant')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = AffiliatePlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed affiliate data for}
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
