<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Events\ReferralAttributedEvent;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraUser\Models\User;
use Spatie\QueueableAction\QueueableAction;

/**
 * A user is attributed once; self-referrals are ignored.
 */
final class AttributeReferralAction
{
    use QueueableAction;

    public function __construct(
        private readonly CreditCommissionAction $creditCommission,
    ) {}

    public function execute(string $code, User $user, ?int $clickId = null): ?AffiliateReferral
    {
        $affiliate = Affiliate::query()->active()->where('code', $code)->first();

        if (! $affiliate instanceof Affiliate || $affiliate->user_id === $user->id) {
            return null;
        }

        /** @var AffiliateReferral $referral */
        $referral = AffiliateReferral::query()->firstOrCreate(['user_id' => $user->id], [
            'affiliate_id' => $affiliate->id,
            'affiliate_click_id' => $clickId,
            'attributed_at' => now(),
        ]);

        if (! $referral->wasRecentlyCreated) {
            return null;
        }

        event(new ReferralAttributedEvent($affiliate->id, $user->id));

        if (ConversionTypeEnum::Signup->isEnabled()) {
            $this->creditCommission->execute(
                affiliate: $affiliate,
                conversionType: ConversionTypeEnum::Signup,
                amount: $affiliate->signupBounty(),
                source: $user,
                referral: $referral,
            );
        }

        return $referral;
    }
}
