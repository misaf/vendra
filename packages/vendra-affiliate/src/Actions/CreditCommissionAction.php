<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Events\AffiliateCommissionEarnedEvent;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Spatie\QueueableAction\QueueableAction;

/**
 * The single writer for the commission ledger. Crediting is idempotent per
 * conversion source: the (conversion_type, source_type, source_id) unique
 * index guarantees repeated events cannot double-credit.
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

        $status = Config::boolean('vendra-affiliate.commissions.auto_approve', true)
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
