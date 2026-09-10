<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Misaf\VendraAffiliate\Database\Factories\AffiliatePayoutFactory;
use Misaf\VendraAffiliate\Enums\PayoutStatusEnum;
use Misaf\VendraAffiliate\Traits\BelongsToAffiliate;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * A settlement grouping the approved commissions that were paid out together;
 * `transaction_id` references the wallet Transaction created for the payout.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $affiliate_id
 * @property int $amount
 * @property PayoutStatusEnum $status
 * @property int|null $transaction_id
 * @property Carbon|null $processed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['affiliate_id', 'amount', 'status', 'transaction_id', 'processed_at'])]
#[Hidden(['tenant_id'])]
#[UseFactory(AffiliatePayoutFactory::class)]
final class AffiliatePayout extends Model implements ShouldLogActivity
{
    use BelongsToAffiliate;
    use BelongsToTenant;

    /** @use HasFactory<AffiliatePayoutFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'affiliate_id' => 'integer',
            'amount' => 'integer',
            'status' => PayoutStatusEnum::class,
            'transaction_id' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
}
