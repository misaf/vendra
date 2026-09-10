<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Console\Commands;

use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;
use Misaf\VendraTagger\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraTagger\TaggerPlugin;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = TaggerPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed tagger data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

    protected $description = 'Seed tagger module data for a tenant';

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
