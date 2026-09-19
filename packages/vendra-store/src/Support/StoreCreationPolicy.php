<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Misaf\VendraStore\Settings\StoreCreationSettings;

/**
 * Shared by the console and reseller panels.
 */
final readonly class StoreCreationPolicy
{
    public function __construct(private StoreCreationSettings $settings) {}

    public function isOpen(): bool
    {
        return $this->settings->open;
    }
}
