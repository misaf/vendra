<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Events\AffiliateCommissionEarnedEvent;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraAffiliate\Settings\AffiliateSettings;
use Spatie\QueueableAction\QueueableAction;

/**
 * A unique index on the conversion source keeps repeated events from double-crediting.
 */
final class CreditCommissionAction
{
    use QueueableAction;

    public function execute(
        Affiliate $affiliate,
        ConversionTypeEnum $conversionType,
        int $amount,
        Model $source,
        ?AffiliateReferral $referral = null,
    ): ?AffiliateCommission {
        if ($amount <= 0 || ! $affiliate->isActive()) {
            return null;
        }

        $status = resolve(AffiliateSettings::class)->auto_approve_commissions
            ? CommissionStatusEnum::Approved
            : CommissionStatusEnum::Pending;

        [$commission, $wasCredited] = DB::transaction(function () use ($affiliate, $conversionType, $amount, $source, $referral, $status): array {
            /** @var AffiliateCommission $commission */
            $commission = AffiliateCommission::withTrashed()->firstOrCreate(
                [
                    'conversion_type' => $conversionType,
                    'source_type' => $source->getMorphClass(),
                    'source_id' => $source->getKey(),
                ],
                [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_referral_id' => $referral?->id,
                    'amount' => $amount,
                    'status' => $status,
                ],
            );

            return [$commission, $commission->wasRecentlyCreated];
        });

        if (! $wasCredited) {
            return null;
        }

        event(new AffiliateCommissionEarnedEvent($affiliate->user_id, $amount));

        return $commission;
    }
}
