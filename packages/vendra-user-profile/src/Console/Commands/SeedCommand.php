<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;
use Misaf\VendraUserProfile\Database\Seeders\PermissionPolicySeeder;

#[Description('Seed user profile module data for a tenant')]
#[Signature('vendra-user-profile:seed
        {tenant? : Tenant ID or slug to seed user profile permissions for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = 'vendra-user-profile';

    /** @return array<string, class-string> */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
