<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraLanguage\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraLanguage\LanguagePlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed language module data for a tenant')]
#[Signature(self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed language data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = LanguagePlugin::ID;

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
