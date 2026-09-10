<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;
use Misaf\VendraVerification\Database\Seeders\PermissionPolicySeeder;

#[Description('Seed verification module data for a tenant')]
#[Signature('vendra-verification:seed
        {tenant? : Tenant ID or slug to seed verification permissions for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = 'vendra-verification';

    /** @return array<string, class-string> */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
