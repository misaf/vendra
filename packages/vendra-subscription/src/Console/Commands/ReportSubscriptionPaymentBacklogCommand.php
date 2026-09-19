<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Context\RequestJobContext;

/**
 * Only observes; {@see RecoverSubscriptionPaymentsCommand} retries payments.
 */
#[Description('Report stuck subscription payments that need attention')]
#[Signature('vendra-subscription:report-payment-backlog {--stale-minutes=30}')]
final class ReportSubscriptionPaymentBacklogCommand extends Command
{
    public function handle(): int
    {
        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'subscription_payment_backlog',
        )->scope(fn (): int => $this->report());

        return self::SUCCESS;
    }

    private function report(): int
    {
        $staleMinutes = (int) $this->option('stale-minutes');
        $staleThreshold = now()->subMinutes($staleMinutes);

        $needsReconciliation = SubscriptionPayment::query()
            ->where('status', SubscriptionPaymentStatus::NeedsReconciliation)
            ->count();

        $stalledProcessing = SubscriptionPayment::query()
            ->where('status', SubscriptionPaymentStatus::Processing)
            ->where('processing_at', '<=', $staleThreshold)
            ->count();

        $activationGap = SubscriptionPayment::query()->awaitingActivation()->count();

        $total = $needsReconciliation + $stalledProcessing + $activationGap;

        $context = [
            'needs_reconciliation' => $needsReconciliation,
            'stalled_processing' => $stalledProcessing,
            'activation_gap' => $activationGap,
            'stale_minutes' => $staleMinutes,
        ];

        if ($total > 0) {
            Log::warning('Subscription payment backlog detected.', $context);
        }

        $this->table(['Metric', 'Count'], [
            ['Needs reconciliation', $needsReconciliation],
            ["Stalled processing (> {$staleMinutes}m)", $stalledProcessing],
            ['Paid but not activated', $activationGap],
            ['Total backlog', $total],
        ]);

        return self::SUCCESS;
    }
}
