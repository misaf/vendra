<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Exceptions\TransactionLimitExceededException;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\Services\TransactionGatewayRegistry;

/**
 * Creates a pending transaction against a wallet, together with its optional
 * fee and metadata rows. Settlement into the ledger only happens later, on the
 * transition to Approved.
 */
final readonly class CreateTransactionAction
{
    public function __construct(private TransactionGatewayRegistry $transactionGatewayRegistry) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        TransactionGateway|string $transactionGateway,
        Wallet $wallet,
        TransactionTypeEnum $transactionType,
        int $amount,
        array $metadata = [],
        ?int $fee = null,
        ?Wallet $counterpartyWallet = null,
        ?string $token = null,
        ?string $idempotencyKey = null,
    ): Transaction {
        throw_if($idempotencyKey !== null && mb_trim($idempotencyKey) === '', InvalidArgumentException::class, 'The transaction idempotency key cannot be empty.');

        $gateway = $transactionGateway instanceof TransactionGateway
            ? $transactionGateway
            : $this->transactionGatewayRegistry->get($transactionGateway);

        return DB::transaction(function () use ($gateway, $wallet, $transactionType, $amount, $metadata, $fee, $counterpartyWallet, $token, $idempotencyKey): Transaction {
            $attributes = [
                'wallet_id' => $wallet->id,
                'transaction_gateway_id' => $gateway->id,
                'counterparty_wallet_id' => $counterpartyWallet?->id,
                'transaction_type' => $transactionType,
                'amount' => abs($amount),
            ];

            if (filled($token)) {
                $attributes['token'] = $token;
            }

            if ($idempotencyKey !== null) {
                $existingTransaction = Transaction::query()
                    ->withTrashed()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingTransaction !== null) {
                    $this->assertMatchingIdempotentTransaction(
                        $existingTransaction,
                        $gateway,
                        $wallet,
                        $transactionType,
                        $amount,
                        $fee,
                        $counterpartyWallet,
                    );

                    return $existingTransaction;
                }
            }

            $this->assertWithinLimit($wallet, $transactionType, $amount);

            $transaction = $idempotencyKey === null
                ? Transaction::query()->create($attributes)
                : Transaction::query()->withTrashed()->firstOrCreate(
                    ['idempotency_key' => $idempotencyKey],
                    $attributes,
                );

            if (! $transaction->wasRecentlyCreated) {
                $this->assertMatchingIdempotentTransaction(
                    $transaction,
                    $gateway,
                    $wallet,
                    $transactionType,
                    $amount,
                    $fee,
                    $counterpartyWallet,
                );

                return $transaction;
            }

            if ($fee !== null && $fee > 0) {
                $transaction->transactionFee()->create(['amount' => $fee]);
            }

            if (filled($metadata)) {
                $this->createTransactionMetadata($transaction, $metadata);
            }

            return $transaction;
        });
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function createTransactionMetadata(Transaction $transaction, array $metadata): void
    {
        $transaction->transactionMetadatas()->createMany(array_map(
            fn (string $key): array => [
                'key_name' => $key,
                'key_value' => $this->stringifyMetadataValue($metadata[$key] ?? null),
            ],
            array_keys($metadata),
        ));
    }

    private function assertWithinLimit(Wallet $wallet, TransactionTypeEnum $transactionType, int $amount): void
    {
        $limit = $wallet->limitFor($transactionType);

        if ($limit !== null && abs($amount) > $limit->amount) {
            throw TransactionLimitExceededException::forWallet($wallet->id, $transactionType, abs($amount), $limit->amount);
        }
    }

    private function assertMatchingIdempotentTransaction(
        Transaction $transaction,
        TransactionGateway $gateway,
        Wallet $wallet,
        TransactionTypeEnum $transactionType,
        int $amount,
        ?int $fee,
        ?Wallet $counterpartyWallet,
    ): void {
        $storedFee = $transaction->transactionFee()->first()?->amount;
        $expectedFee = $fee !== null && $fee > 0 ? $fee : null;
        $matches = $transaction->transaction_gateway_id === $gateway->id
            && $transaction->wallet_id === $wallet->id
            && $transaction->transaction_type === $transactionType
            && $transaction->amount === abs($amount)
            && $transaction->counterparty_wallet_id === $counterpartyWallet?->id
            && $storedFee === $expectedFee;

        if (! $matches) {
            throw new LogicException("Transaction idempotency key [{$transaction->idempotency_key}] was already used for different charge details.");
        }
    }

    private function stringifyMetadataValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
