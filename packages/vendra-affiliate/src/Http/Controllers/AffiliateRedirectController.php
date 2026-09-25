<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Misaf\VendraAffiliate\Actions\RecordAffiliateClickAction;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Settings\AffiliateSettings;

/**
 * Unknown codes redirect silently so the endpoint cannot be used to probe codes.
 */
final readonly class AffiliateRedirectController
{
    public function __construct(
        private RecordAffiliateClickAction $recordAffiliateClick,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $redirectUrl = resolve(AffiliateSettings::class)->redirect_url;

        $affiliate = Affiliate::query()->active()->where('code', $code)->first();

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
            resolve(AffiliateSettings::class)->cookie_ttl_days * 24 * 60,
        );

        return redirect($redirectUrl);
    }
}
