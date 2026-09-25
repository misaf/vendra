<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionPaymentStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Processing = 'processing';
    case RequiresAction = 'requires_action';
    case Paid = 'paid';
    case Failed = 'failed';
    case NeedsReconciliation = 'needs_reconciliation';
    case Cancelled = 'cancelled';
    case Refunding = 'refunding';
    case Refunded = 'refunded';
    case RefundFailed = 'refund_failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Failed, self::Cancelled, self::Refunded], true);
    }

    public function getLabel(): string
    {
        return __('vendra-subscription::enums.subscription_payment_status_'.$this->value);
    }
}
