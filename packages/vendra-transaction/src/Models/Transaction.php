<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraSupport\Capabilities\HasOptionalTags;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Events\TransactionDeclined;
use Misaf\VendraTransaction\Events\TransactionFailed;
use Misaf\VendraTransaction\States\Approved;
use Misaf\VendraTransaction\States\Declined;
use Misaf\VendraTransaction\States\Failed;
use Misaf\VendraTransaction\States\Pending;
use Misaf\VendraTransaction\States\Processing;
use Misaf\VendraTransaction\States\Review;
use Misaf\VendraTransaction\States\TransactionState;
use Spatie\ModelStates\HasStates;

/**
 * A gateway-facing money movement against a wallet. The `amount` is always
 * the absolute value in the wallet currency's minor units; the sign of the
 * eventual ledger entry derives from the transaction type. Settlement into
 * the ledger happens exactly once, on the transition to Approved.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $wallet_id
 * @property int $transaction_gateway_id
 * @property int|null $counterparty_wallet_id
 * @property TransactionTypeEnum $transaction_type
 * @property string $token
 * @property string|null $idempotency_key
 * @property int $amount
 * @property TransactionState $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['wallet_id', 'transaction_gateway_id', 'counterparty_wallet_id', 'transaction_type', 'token', 'idempotency_key', 'amount', 'status'])]
#[Hidden(['tenant_id', 'idempotency_key'])]
#[UseFactory(TransactionFactory::class)]
final class Transaction extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    use HasOptionalTags;
    use HasStates;
    use SoftDeletes;

    private const string TOKEN_CHARACTERS = '123456789';

    private const int TOKEN_LENGTH = 20;

    public const string TAG_TYPE = 'transaction';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'wallet_id' => 'integer',
            'transaction_gateway_id' => 'integer',
            'counterparty_wallet_id' => 'integer',
            'transaction_type' => TransactionTypeEnum::class,
            'token' => 'string',
            'idempotency_key' => 'string',
            'amount' => 'integer',
            'status' => TransactionState::class,
        ];
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function counterpartyWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counterparty_wallet_id');
    }

    /**
     * @return BelongsTo<TransactionGateway, $this>
     */
    public function transactionGateway(): BelongsTo
    {
        return $this->belongsTo(TransactionGateway::class);
    }

    /**
     * @return HasOne<TransactionFee, $this>
     */
    public function transactionFee(): HasOne
    {
        return $this->hasOne(TransactionFee::class);
    }

    /**
     * @return HasMany<TransactionMetadata, $this>
     */
    public function transactionMetadatas(): HasMany
    {
        return $this->hasMany(TransactionMetadata::class);
    }

    /**
     * @return MorphMany<LedgerEntry, $this>
     */
    public function ledgerEntries(): MorphMany
    {
        return $this->morphMany(LedgerEntry::class, 'source');
    }

    public function approve(): void
    {
        $this->status->transitionTo(Approved::class);
    }

    public function decline(): void
    {
        $this->status->transitionTo(Declined::class);

        event(new TransactionDeclined($this));
    }

    public function fail(): void
    {
        $this->status->transitionTo(Failed::class);

        event(new TransactionFailed($this));
    }

    public function markProcessing(): void
    {
        $this->status->transitionTo(Processing::class);
    }

    public function markReview(): void
    {
        $this->status->transitionTo(Review::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function ofType(Builder $builder, TransactionTypeEnum $transactionType): void
    {
        $builder->where('transaction_type', $transactionType);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function deposit(Builder $builder): void
    {
        $builder->where('transaction_type', TransactionTypeEnum::Deposit);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function withdrawal(Builder $builder): void
    {
        $builder->where('transaction_type', TransactionTypeEnum::Withdrawal);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function commission(Builder $builder): void
    {
        $builder->where('transaction_type', TransactionTypeEnum::Commission);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function bonus(Builder $builder): void
    {
        $builder->where('transaction_type', TransactionTypeEnum::Bonus);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function transfer(Builder $builder): void
    {
        $builder->where('transaction_type', TransactionTypeEnum::Transfer);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function approved(Builder $builder): void
    {
        $builder->whereState('status', Approved::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function declined(Builder $builder): void
    {
        $builder->whereState('status', Declined::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function failed(Builder $builder): void
    {
        $builder->whereState('status', Failed::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function pending(Builder $builder): void
    {
        $builder->whereState('status', Pending::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function review(Builder $builder): void
    {
        $builder->whereState('status', Review::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function processing(Builder $builder): void
    {
        $builder->whereState('status', Processing::class);
    }

    protected function tagType(): string
    {
        return self::TAG_TYPE;
    }

    protected static function booted(): void
    {
        self::creating(function (self $transaction): void {
            if (blank($transaction->token)) {
                $transaction->token = self::generateToken();
            }
        });
    }

    /**
     * A transaction reference for humans to quote: digits only, no zero, so it
     * survives being read aloud or copied off a receipt.
     */
    private static function generateToken(): string
    {
        $maxIndex = mb_strlen(self::TOKEN_CHARACTERS) - 1;
        $token = '';

        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= self::TOKEN_CHARACTERS[random_int(0, $maxIndex)];
        }

        return $token;
    }
}
