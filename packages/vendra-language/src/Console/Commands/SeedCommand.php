<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Console\Commands;

use Misaf\VendraLanguage\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraLanguage\LanguagePlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = LanguagePlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed language data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}';

    protected $description = 'Seed language module data for a tenant';

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
