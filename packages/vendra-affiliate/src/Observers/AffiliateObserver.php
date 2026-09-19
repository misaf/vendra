<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Observers;

use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Services\AffiliateCodeService;

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
