<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Services;

use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use RuntimeException;

/**
 * Look-up of the active payment gateways, and of the built-in internal one that
 * moves money between wallets without leaving the platform.
 *
 * Split out of the former TransactionService so that callers needing a gateway
 * no longer also receive wallet resolution and limit look-up.
 */
final class TransactionGatewayRegistry
{
    public const string INTERNAL_GATEWAY_SLUG = 'internal-transactions';

    /**
     * Whether any *external* gateway is active. The internal one is always
     * present, so counting it would make this answer meaningless.
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

        if ($gateway === null) {
            throw new RuntimeException("No active transaction gateway found for slug [{$slug}].");
        }

        return $gateway;
    }

    public function isInternal(Transaction $transaction): bool
    {
        return $this->get(self::INTERNAL_GATEWAY_SLUG)->is($transaction->transactionGateway);
    }
}
