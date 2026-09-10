<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Observers\AffiliateObserver;
use Misaf\VendraSupport\Capabilities\HasOptionalTags;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUser\Traits\BelongsToUser;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property string $code
 * @property int $commission_percent
 * @property int|null $signup_bounty
 * @property AffiliateStatusEnum $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['user_id', 'code', 'commission_percent', 'signup_bounty', 'status'])]
#[Hidden(['tenant_id'])]
#[ObservedBy([AffiliateObserver::class])]
#[UseFactory(AffiliateFactory::class)]
final class Affiliate extends Model implements ShouldLogActivity
{
    use BelongsToTenant;
    use BelongsToUser;

    /** @use HasFactory<AffiliateFactory> */
    use HasFactory;

    use HasOptionalTags;
    use SoftDeletes;

    public const string TAG_TYPE = 'affiliate';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_id' => 'integer',
            'code' => 'string',
            'commission_percent' => 'integer',
            'signup_bounty' => 'integer',
            'status' => AffiliateStatusEnum::class,
        ];
    }

    /**
     * @return HasMany<AffiliateClick, $this>
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    /**
     * @return HasMany<AffiliateReferral, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    /**
     * @return HasMany<AffiliatePayout, $this>
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function isActive(): bool
    {
        return $this->status === AffiliateStatusEnum::Active;
    }

    /**
     * The approved-but-unpaid commission total in minor units.
     */
    public function pendingBalance(): int
    {
        $pendingBalance = $this->getAttributes()['pending_balance'] ?? null;

        if (is_numeric($pendingBalance)) {
            return (int) $pendingBalance;
        }

        return (int) $this->commissions()
            ->where('status', CommissionStatusEnum::Approved)
            ->whereNull('affiliate_payout_id')
            ->sum('amount');
    }

    /**
     * The bounty credited for an attributed signup, in minor units.
     */
    public function signupBounty(): int
    {
        return $this->signup_bounty ?? Config::integer('vendra-affiliate.defaults.signup_bounty', 0);
    }

    public function referralUrl(): string
    {
        return route('affiliate.redirect', ['code' => $this->code]);
    }

    protected function tagType(): string
    {
        return self::TAG_TYPE;
    }
}
