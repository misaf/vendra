<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Console\Attributes\Signature;
use Misaf\VendraSupport\Tenancy\Console\Commands\TenantSeedCommand;

#[Signature('support-test:seed {tenant?} {seeders?*}')]
final class SupportTestSeedCommand extends TenantSeedCommand
{
    public static function seeders(): array
    {
        return ['records' => SupportTestTenantSeeder::class];
    }
}
