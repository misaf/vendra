<?php

declare(strict_types=1);

namespace Misaf\VendraFaq\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraFaq\Database\Seeders\DemoContentSeeder;
use Misaf\VendraFaq\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraFaq\FaqPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed FAQ module data for a tenant')]
#[Signature(self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed FAQ data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies, demo-contents}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = FaqPlugin::ID;

    /**
     * @return array<string, class-string>
     */
    public static function seeders(): array
    {
        return [
            'permission-policies' => PermissionPolicySeeder::class,
            'demo-contents' => DemoContentSeeder::class,
        ];
    }
}
