<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Validates a submitted amount against the wallet's configured per-type
 * transaction limit, when one exists.
 */
final readonly class WithinTransactionLimit implements ValidationRule
{
    public function __construct(
        private Wallet $wallet,
        private TransactionTypeEnum $transactionType,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $limit = $this->wallet->limitFor($this->transactionType);

        if ($limit !== null && abs((int) $value) > $limit->amount) {
            $fail('vendra-transaction::validation.amount_exceeds_limit')->translate([
                'limit' => $limit->amount,
            ]);
        }
    }
}
