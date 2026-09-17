<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionStatus: string implements HasColor, HasLabel
{
    case PendingPayment = 'pending_payment';
    case Active = 'active';
    case PastDue = 'past_due';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::PendingPayment => 'warning',
            self::PastDue, self::Expired => 'danger',
            self::Cancelled => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PendingPayment => __('vendra-subscription::enums.subscription_status_pending_payment'),
            self::Active => __('vendra-subscription::enums.subscription_status_active'),
            self::PastDue => __('vendra-subscription::enums.subscription_status_past_due'),
            self::Expired => __('vendra-subscription::enums.subscription_status_expired'),
            self::Cancelled => __('vendra-subscription::enums.subscription_status_cancelled'),
        };
    }
}
