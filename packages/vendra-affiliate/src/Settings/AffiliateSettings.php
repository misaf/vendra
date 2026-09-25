<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Settings;

use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Spatie\LaravelSettings\Settings;

final class AffiliateSettings extends Settings
{
    public int $commission_percent;

    /**
     * The minor units credited per referred signup when the affiliate sets no bounty of its own.
     */
    public int $signup_bounty;

    public bool $deposit_conversions;

    public bool $signup_conversions;

    public bool $checkout_conversions;

    public bool $auto_approve_commissions;

    public int $cookie_ttl_days;

    public string $redirect_url;

    /**
     * The smallest approved balance, in minor units, that is paid out.
     */
    public int $payout_minimum;

    public static function group(): string
    {
        return 'affiliate';
    }

    public function earnsOn(ConversionTypeEnum $conversionType): bool
    {
        return match ($conversionType) {
            ConversionTypeEnum::Deposit => $this->deposit_conversions,
            ConversionTypeEnum::Signup => $this->signup_conversions,
            ConversionTypeEnum::Checkout => $this->checkout_conversions,
        };
    }
}
