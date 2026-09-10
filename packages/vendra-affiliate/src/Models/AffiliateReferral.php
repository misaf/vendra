<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Traits\BelongsToAffiliate;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUser\Traits\BelongsToUser;

/**
 * The attribution record linking a referred user to the affiliate who
 * recruited them; each user can be attributed to at most one affiliate.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $affiliate_id
 * @property int $user_id
 * @property int|null $affiliate_click_id
 * @property Carbon $attributed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['affiliate_id', 'user_id', 'affiliate_click_id', 'attributed_at'])]
#[Hidden(['tenant_id'])]
#[UseFactory(AffiliateReferralFactory::class)]
final class AffiliateReferral extends Model implements ShouldLogActivity
{
    use BelongsToAffiliate;
    use BelongsToTenant;
    use BelongsToUser;

    /** @use HasFactory<AffiliateReferralFactory> */
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
            'user_id' => 'integer',
            'affiliate_click_id' => 'integer',
            'attributed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AffiliateClick, $this>
     */
    public function click(): BelongsTo
    {
        return $this->belongsTo(AffiliateClick::class, 'affiliate_click_id');
    }
}
