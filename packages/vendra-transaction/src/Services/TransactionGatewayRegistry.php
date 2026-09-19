<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Misaf\VendraTransaction\Models\TransactionGateway;
use RuntimeException;

final class TransactionGatewayRegistry
{
    public const string INTERNAL_GATEWAY_SLUG = 'internal-transactions';

    public function hasActive(string $slug): bool
    {
        return TransactionGateway::query()
            ->active()
            ->where('slug', $slug)
            ->exists();
    }

    public function get(string $slug): TransactionGateway
    {
        $gateway = TransactionGateway::query()
            ->active()
            ->where('slug', $slug)
            ->first();

        throw_if($gateway === null, RuntimeException::class, "No active transaction gateway found for slug [{$slug}].");

        return $gateway;
    }
}
