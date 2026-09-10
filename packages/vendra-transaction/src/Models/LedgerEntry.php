<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraTransaction\Database\Factories\LedgerEntryFactory;
use Misaf\VendraTransaction\Observers\LedgerEntryObserver;

/**
 * An immutable, signed movement on a wallet. Each entry snapshots the
 * balance it produced, so the ledger is a complete audit trail and the
 * cached wallet balance can always be re-derived and verified.
 *
 * @property int $id
 * @property int $wallet_id
 * @property string|null $source_type
 * @property int|null $source_id
 * @property int $amount
 * @property int $balance_after
 * @property Carbon $created_at
 */
#[Fillable(['amount', 'balance_after'])]
#[ObservedBy([LedgerEntryObserver::class])]
#[UseFactory(LedgerEntryFactory::class)]
final class LedgerEntry extends Model
{
    /** @use HasFactory<LedgerEntryFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'wallet_id' => 'integer',
            'source_id' => 'integer',
            'amount' => 'integer',
            'balance_after' => 'integer',
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
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
