<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Console\Commands;

use Misaf\VendraNewsletter\Database\Seeders\DemoContentSeeder;
use Misaf\VendraNewsletter\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraNewsletter\NewsletterPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = NewsletterPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed newsletter data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed newsletter module data for a tenant';

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
