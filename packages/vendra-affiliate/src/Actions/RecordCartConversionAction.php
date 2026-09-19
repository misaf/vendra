<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraUser\Models\User;
use Spatie\QueueableAction\QueueableAction;

/**
 * vendra-cart has no checkout event yet, so hosts call this with the order total.
 */
final class RecordCartConversionAction
{
    use QueueableAction;

    public function __construct(
        private readonly CreditCommissionAction $creditCommission,
    ) {}

    public function execute(User $buyer, Model $source, int $totalMinor): ?AffiliateCommission
    {
        if (! ConversionTypeEnum::Checkout->isEnabled() || $totalMinor <= 0) {
            return null;
        }

        $referral = AffiliateReferral::with('affiliate')
            ->where('user_id', $buyer->id)
            ->first();

        if (! $referral instanceof AffiliateReferral || $referral->affiliate === null) {
            return null;
        }

        $affiliate = $referral->affiliate;

        return $this->creditCommission->execute(
            affiliate: $affiliate,
            conversionType: ConversionTypeEnum::Checkout,
            amount: intdiv($totalMinor * $affiliate->commission_percent, 100),
            source: $source,
            referral: $referral,
        );
    }
}
