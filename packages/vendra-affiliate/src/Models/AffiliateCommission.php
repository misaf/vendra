<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraAffiliate\Database\Factories\AffiliateCommissionFactory;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Traits\BelongsToAffiliate;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * A ledger entry crediting an affiliate for a single conversion; the source
 * morph points at the record that produced it (transaction, user, or cart)
 * and is unique per conversion type to keep crediting idempotent.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $affiliate_id
 * @property int|null $affiliate_referral_id
 * @property ConversionTypeEnum $conversion_type
 * @property string|null $source_type
 * @property int|null $source_id
 * @property int $amount
 * @property CommissionStatusEnum $status
 * @property int|null $affiliate_payout_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'affiliate_id',
    'affiliate_referral_id',
    'conversion_type',
    'source_type',
    'source_id',
    'amount',
    'status',
    'affiliate_payout_id',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(AffiliateCommissionFactory::class)]
final class AffiliateCommission extends Model implements ShouldLogActivity
{
    use BelongsToAffiliate;
    use BelongsToTenant;

    /** @use HasFactory<AffiliateCommissionFactory> */
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
            'affiliate_id' => 'integer',
            'affiliate_referral_id' => 'integer',
            'conversion_type' => ConversionTypeEnum::class,
            'source_id' => 'integer',
            'amount' => 'integer',
            'status' => CommissionStatusEnum::class,
            'affiliate_payout_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<AffiliateReferral, $this>
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(AffiliateReferral::class, 'affiliate_referral_id');
    }

    /**
     * @return BelongsTo<AffiliatePayout, $this>
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class, 'affiliate_payout_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPayable(): bool
    {
        return $this->status === CommissionStatusEnum::Approved
            && $this->affiliate_payout_id === null;
    }
}
