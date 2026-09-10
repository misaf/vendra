<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Misaf\VendraTransaction\Database\Factories\TransactionLimitFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;

/**
 * A per-wallet, per-type ceiling on single transaction amounts, expressed
 * in the wallet currency's minor units.
 *
 * @property int $id
 * @property int $wallet_id
 * @property TransactionTypeEnum $transaction_type
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['wallet_id', 'transaction_type', 'amount'])]
#[UseFactory(TransactionLimitFactory::class)]
final class TransactionLimit extends Model
{
    /** @use HasFactory<TransactionLimitFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'wallet_id' => 'integer',
            'transaction_type' => TransactionTypeEnum::class,
            'amount' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
