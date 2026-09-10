<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Misaf\VendraTransaction\Database\Factories\TransactionFeeFactory;

/**
 * The fee charged for a transaction, stored as a positive amount in the
 * wallet currency's minor units. It settles as its own negative ledger
 * entry when the transaction is approved.
 *
 * @property int $id
 * @property int $transaction_id
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['transaction_id', 'amount'])]
#[UseFactory(TransactionFeeFactory::class)]
final class TransactionFee extends Model
{
    /** @use HasFactory<TransactionFeeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'transaction_id' => 'integer',
            'amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
