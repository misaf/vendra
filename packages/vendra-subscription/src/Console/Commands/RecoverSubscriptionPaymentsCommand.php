<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Jobs\ProcessSubscriptionPayment;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Context\RequestJobContext;

#[Description('Requeue stale or interrupted subscription payment operations')]
#[Signature('vendra-subscription:recover-payments')]
final class RecoverSubscriptionPaymentsCommand extends Command
{
    public function handle(): int
    {
        $count = 0;

        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'subscription_payment_recovery',
        )->scope(function () use (&$count): void {
            SubscriptionPayment::query()
                ->where(function (Builder $query): void {
                    $query
                        ->where(function (Builder $query): void {
                            $query
                                ->where('status', SubscriptionPaymentStatus::Pending)
                                ->where(function (Builder $query): void {
                                    $query
                                        ->whereNull('next_retry_at')
                                        ->orWhere('next_retry_at', '<=', now());
                                });
                        })
                        ->orWhere(function (Builder $query): void {
                            $query
                                ->whereIn('status', [
                                    SubscriptionPaymentStatus::Processing,
                                    SubscriptionPaymentStatus::NeedsReconciliation,
                                ])
                                ->where(function (Builder $query): void {
                                    $query
                                        ->whereNull('next_retry_at')
                                        ->orWhere('next_retry_at', '<=', now());
                                });
                        })
                        ->orWhere(function (Builder $query): void {
                            $query
                                ->where('status', SubscriptionPaymentStatus::Paid)
                                ->whereHas('subscription', fn (Builder $query): Builder => $query->where('status', SubscriptionStatus::PendingPayment));
                        });
                })
                ->select('id')
                ->chunkById(100, function ($payments) use (&$count): void {
                    foreach ($payments as $payment) {
                        dispatch(new ProcessSubscriptionPayment($payment->id));
                        $count++;
                    }
                });
        });

        $this->info("Requeued {$count} subscription payment(s).");

        return self::SUCCESS;
    }
}
