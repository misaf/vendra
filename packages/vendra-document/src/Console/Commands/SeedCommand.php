<?php

declare(strict_types=1);

namespace Misaf\VendraDocument\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraDocument\Database\Seeders\PermissionPolicySeeder;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Description('Seed document module data for a tenant')]
#[Signature('vendra-document:seed
        {tenant? : Tenant ID or slug to seed document permissions for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = 'vendra-document';

    /** @return array<string, class-string> */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
