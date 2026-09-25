<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Misaf\VendraTransaction\Models\TransactionGateway;
use RuntimeException;

final class TransactionGatewayRegistry
{
    public const string INTERNAL_GATEWAY_SLUG = 'internal-transactions';

    public function hasActive(string $slug): bool
    {
        return $this->activeQuery($slug)->exists();
    }

    public function get(string $slug): TransactionGateway
    {
        $gateway = $this->activeQuery($slug)->first();

        throw_if($gateway === null, RuntimeException::class, "No active transaction gateway found for slug [{$slug}].");

        return $gateway;
    }

    /**
     * Outside a tenant the tenant scope filters nothing, so the platform's own
     * gateways, which carry a null tenant id, are selected explicitly rather
     * than whichever tenant's gateway shares the slug.
     *
     * @return Builder<TransactionGateway>
     */
    private function activeQuery(string $slug): Builder
    {
        $gateway = new TransactionGateway;

        return TransactionGateway::query()
            ->active()
            ->where('slug', $slug)
            ->when(
                TenantAwareness::currentId() === null && TenantSchema::hasTenantColumn($gateway->getTable()),
                fn (Builder $query): Builder => $query->whereNull($gateway->qualifyColumn(TenantSchema::column())),
            );
    }
}
