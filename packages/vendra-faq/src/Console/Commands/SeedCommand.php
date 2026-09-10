<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Console\Commands;

use Misaf\VendraFaq\Database\Seeders\DemoContentSeeder;
use Misaf\VendraFaq\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraFaq\FaqPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = FaqPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed FAQ data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed FAQ module data for a tenant';

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
