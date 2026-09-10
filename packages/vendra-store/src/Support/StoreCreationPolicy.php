<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Settings\StoreCreationSettings;

/**
 * The one rule that says whether the platform is creating stores at all.
 *
 * Both panels that create stores consume it: the console, where it is the whole
 * gate, and the reseller panel, where it is the platform half of a gate whose
 * other half is the reseller's own subscription. Keeping it here is what lets
 * both read it without `vendra-reseller` ever pointing at `vendra-console`.
 */
final readonly class StoreCreationPolicy
{
    public function __construct(private StoreCreationSettings $settings) {}

    public function isOpen(): bool
    {
        return $this->settings->open;
    }
}
