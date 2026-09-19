<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use RuntimeException;

final class TransactionGatewayRegistry
{
    public const string INTERNAL_GATEWAY_SLUG = 'internal-transactions';

    /**
     * Determine if any external gateway is active.
     */
    public function hasAnyActive(): bool
    {
        return TransactionGateway::query()
            ->active()
            ->where('slug', '<>', self::INTERNAL_GATEWAY_SLUG)
            ->exists();
    }

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

    public function isInternal(Transaction $transaction): bool
    {
        return $this->get(self::INTERNAL_GATEWAY_SLUG)->is($transaction->transactionGateway);
    }
}
