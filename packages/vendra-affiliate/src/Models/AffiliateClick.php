<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Misaf\VendraAffiliate\Database\Factories\AffiliateClickFactory;
use Misaf\VendraAffiliate\Traits\BelongsToAffiliate;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * An immutable click-log entry recorded when a visitor follows a referral link.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $affiliate_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string|null $landing_url
 * @property Carbon $created_at
 */
#[Fillable(['affiliate_id', 'ip_address', 'user_agent', 'referer', 'landing_url'])]
#[Hidden(['tenant_id'])]
#[UseFactory(AffiliateClickFactory::class)]
final class AffiliateClick extends Model
{
    use BelongsToAffiliate;
    use BelongsToTenant;

    /** @use HasFactory<AffiliateClickFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'affiliate_id' => 'integer',
            'ip_address' => 'string',
            'user_agent' => 'string',
            'referer' => 'string',
            'landing_url' => 'string',
        ];
    }
}
