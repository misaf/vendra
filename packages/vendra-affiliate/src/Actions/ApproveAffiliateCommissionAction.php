<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;

final class ApproveAffiliateCommissionAction
{
    /**
     * @throws LogicException
     */
    public function execute(AffiliateCommission $commission): AffiliateCommission
    {
        return DB::transaction(function () use ($commission): AffiliateCommission {
            $lockedCommission = $commission->refreshForUpdate();

            throw_unless($lockedCommission->status === CommissionStatusEnum::Pending, LogicException::class, "Commission [{$lockedCommission->id}] is no longer pending.");

            $lockedCommission->update(['status' => CommissionStatusEnum::Approved]);

            return $lockedCommission;
        });
    }
}
