<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Observers;

use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Services\AffiliateCodeService;

/**
 * Deliberately synchronous: `creating` mutates the model before the insert, so
 * the work has to happen inline. A queued observer would run after the row
 * already exists and the assignment would be lost.
 *
 * Living here rather than in a `booted()` closure is what lets the code service
 * arrive through the constructor instead of a container lookup inside the model.
 */
final readonly class AffiliateObserver
{
    public function __construct(private AffiliateCodeService $affiliateCodeService) {}

    public function creating(Affiliate $affiliate): void
    {
        if (blank($affiliate->getAttribute('code'))) {
            $affiliate->code = $this->affiliateCodeService->generate();
        }
    }
}
