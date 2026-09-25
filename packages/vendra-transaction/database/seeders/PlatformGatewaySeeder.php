<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Seeders;

use Illuminate\Database\Seeder;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Services\TransactionGatewayRegistry;

/**
 * Create the tenantless internal gateway the platform ledger moves money
 * through, such as reseller wallet credits and subscription charges.
 */
final class PlatformGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateway = new TransactionGateway;

        TransactionGateway::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->where('slug', TransactionGatewayRegistry::INTERNAL_GATEWAY_SLUG)
            ->when(
                TenantSchema::hasTenantColumn($gateway->getTable()),
                fn ($query) => $query->whereNull($gateway->qualifyColumn(TenantSchema::column())),
            )
            ->firstOr(fn (): TransactionGateway => TransactionGateway::query()->create([
                'name' => ['en' => 'Internal Transactions'],
                'slug' => TransactionGatewayRegistry::INTERNAL_GATEWAY_SLUG,
                'active' => true,
            ]));
    }
}
