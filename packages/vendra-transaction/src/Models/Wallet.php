<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Support\TransactionUsers;

/**
 * A per-user, per-currency balance holder. The cached `balance` column is
 * only ever written through the ledger, which records every movement as an
 * immutable entry.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property string $currency_code
 * @property int $balance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['user_id', 'currency_code'])]
#[Hidden(['tenant_id'])]
#[UseFactory(WalletFactory::class)]
final class Wallet extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'currency_code' => 'string',
            'balance' => 'integer',
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function currencyCode(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::upper($value),
        );
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TransactionUsers::model(), 'user_id');
    }

    /**
     * @return HasMany<LedgerEntry, $this>
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<TransactionLimit, $this>
     */
    public function transactionLimits(): HasMany
    {
        return $this->hasMany(TransactionLimit::class);
    }

    /**
     * The limit governing one kind of movement on this wallet, if one is set.
     *
     * A wallet's own limits are wallet state, so the lookup belongs here rather
     * than on a service that callers had to reach for separately.
     */
    public function limitFor(TransactionTypeEnum $transactionType): ?TransactionLimit
    {
        return $this->transactionLimits()
            ->where('transaction_type', $transactionType)
            ->first();
    }
}
