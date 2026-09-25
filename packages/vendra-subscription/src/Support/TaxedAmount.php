<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Misaf\VendraSubscription\Contracts\BillingProfile;

final readonly class TaxedAmount
{
    private function __construct(
        public int $net,
        public int $rate,
        public int $tax,
        public int $total,
    ) {}

    /**
     * Add tax at a rate in basis points, rounding half up to the minor unit.
     */
    public static function for(int $net, int $rate): self
    {
        $tax = intdiv($net * $rate + 5_000, 10_000);

        return new self($net, $rate, $tax, $net + $tax);
    }

    public static function withProfileTax(int $net): self
    {
        return self::for($net, resolve(BillingProfile::class)->taxRate());
    }
}
