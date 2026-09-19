<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

/**
 * The row lock serializes this against ProcessAffiliatePayoutAction, so a
 * commission that was just paid out is never reversed.
 */
final class ReverseAffiliateCommissionAction
{
    /**
     * @throws LogicException
     */
    public function execute(AffiliateCommission $commission): AffiliateCommission
    {
        return DB::transaction(function () use ($commission): AffiliateCommission {
            $lockedCommission = $commission->refreshForUpdate();

            throw_unless($lockedCommission->canBeReversed(), LogicException::class, "Commission [{$lockedCommission->id}] can no longer be reversed.");

            $lockedCommission->update(['status' => CommissionStatusEnum::Reversed]);

            return $lockedCommission;
        });
    }
}
