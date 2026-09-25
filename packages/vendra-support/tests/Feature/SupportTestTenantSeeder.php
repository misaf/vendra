<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Illuminate\Database\Seeder;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class SupportTestTenantSeeder extends Seeder
{
    /**
     * @var list<int|null>
     */
    public static array $seededTenants = [];

    public function run(TenantResolver $tenants): void
    {
        self::$seededTenants[] = $tenants->currentId();
    }
}
