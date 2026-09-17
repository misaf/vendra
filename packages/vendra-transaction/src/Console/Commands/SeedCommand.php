<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;
use Misaf\VendraTransaction\Database\Seeders\PermissionPolicySeeder;

#[Description('Seed transaction module data for a tenant')]
#[Signature('vendra-transaction:seed
        {tenant? : Tenant ID or slug to seed transaction permissions for}
        {seeders?* : Seeder keys to run. Use "all" or: permission-policies}')]
final class SeedCommand extends TenantSeedCommand
{
    protected const string MODULE_NAME = 'vendra-transaction';

    /**
     * @return array<string, class-string>
     */
    protected function seeders(): array
    {
        return ['permission-policies' => PermissionPolicySeeder::class];
    }
}
