<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Misaf\VendraSubscription\Actions\IssueSubscriptionInvoiceAction;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;

/**
 * Queued, so an invoice that cannot be issued is retried on its own and never
 * holds up the activation the same payment triggers.
 */
final class IssueInvoiceOnPayment implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(private readonly IssueSubscriptionInvoiceAction $issueSubscriptionInvoiceAction) {}

    public function shouldQueue(SubscriptionPaymentPaid $event): bool
    {
        return $event->payment->amount > 0;
    }

    public function handle(SubscriptionPaymentPaid $event): void
    {
        $this->issueSubscriptionInvoiceAction->execute($event->payment);
    }
}
