<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Console\Commands;

use Misaf\VendraInquiry\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraInquiry\InquiryPlugin;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = InquiryPlugin::ID;

    protected $signature = self::MODULE_NAME.':seed
        {tenant? : Tenant ID or slug to seed inquiry data for}
        {seeders?* : Seeder keys to run. Use "all" or one or more of: permission-policies}';

    protected $description = 'Seed inquiry module data for a tenant';

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
