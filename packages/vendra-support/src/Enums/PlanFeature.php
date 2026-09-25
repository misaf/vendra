<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The on/off entitlements a plan can grant, stored in `plans.features`.
 */
enum PlanFeature: string implements HasLabel
{
    case CustomDomain = 'custom_domain';
    case PrioritySupport = 'priority_support';

    public function getLabel(): string
    {
        return match ($this) {
            self::CustomDomain => __('vendra-support::entitlements.feature_custom_domain'),
            self::PrioritySupport => __('vendra-support::entitlements.feature_priority_support'),
        };
    }
}
