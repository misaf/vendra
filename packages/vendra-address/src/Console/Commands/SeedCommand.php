<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraAddress\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed address module data for a tenant')]
#[Signature('vendra-address:seed
        {tenant? : Tenant ID or slug to seed address permissions for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = 'vendra-address';

    /** @return array<string, class-string> */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
