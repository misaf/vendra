<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Misaf\VendraAffiliate\Actions\RecordAffiliateClickAction;
use Misaf\VendraAffiliate\Enums\AffiliateStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;

/**
 * Records a referral-link click and drops the attribution cookie before
 * redirecting the visitor to the configured landing URL. Unknown codes
 * redirect silently so the endpoint cannot be used to probe codes.
 */
final readonly class AffiliateRedirectController
{
    public function __construct(
        private RecordAffiliateClickAction $recordAffiliateClick,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $redirectUrl = Config::string('vendra-affiliate.attribution.redirect_url', '/');

        $affiliate = Affiliate::query()->where('code', $code)
            ->where('status', AffiliateStatusEnum::Active)
            ->first();

        if (! $affiliate instanceof Affiliate) {
            return redirect($redirectUrl);
        }

        $click = $this->recordAffiliateClick->execute(
            affiliate: $affiliate,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            referer: $request->headers->get('referer'),
            landingUrl: $request->fullUrl(),
        );

        Cookie::queue(
            Config::string('vendra-affiliate.attribution.cookie_name', 'vendra_affiliate_ref'),
            sprintf('%s|%d', $affiliate->code, $click->id),
            Config::integer('vendra-affiliate.attribution.cookie_ttl_days', 30) * 24 * 60,
        );

        return redirect($redirectUrl);
    }
}
