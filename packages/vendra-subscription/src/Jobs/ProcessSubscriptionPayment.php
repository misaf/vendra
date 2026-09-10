<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Misaf\VendraSubscription\Actions\ChargeSubscriptionAction;
use Misaf\VendraSubscription\Context\SubscriptionContextKeys;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Context\RequestJobContext;
use Throwable;

/**
 * Processes one durable subscription payment. The engine stays multitenancy
 * agnostic: applications dispatching it from a context without a current tenant
 * register it under multitenancy's not_tenant_aware_jobs rather than the job
 * coupling itself to a tenancy provider.
 */
final class ProcessSubscriptionPayment implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    public int $uniqueFor = 600;

    public function __construct(public readonly int $paymentId) {}

    public function handle(ChargeSubscriptionAction $chargeSubscriptionAction): void
    {
        $payment = SubscriptionPayment::query()->find($this->paymentId);

        if (! $payment instanceof SubscriptionPayment) {
            return;
        }

        $this->context($payment)->scope(
            function () use ($chargeSubscriptionAction, $payment): void {
                $chargeSubscriptionAction->execute($payment);
            },
        );
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 30, 120, 300];
    }

    public function uniqueId(): string
    {
        return (string) $this->paymentId;
    }

    public function failed(?Throwable $exception): void
    {
        $payment = SubscriptionPayment::query()->find($this->paymentId);
        $context = $payment instanceof SubscriptionPayment
            ? $this->context($payment)
            : new RequestJobContext(metadata: [SubscriptionContextKeys::PAYMENT_ID => $this->paymentId]);

        $context->scope(function () use ($exception): void {
            DB::transaction(function () use ($exception): void {
                $payment = SubscriptionPayment::query()
                    ->whereKey($this->paymentId)
                    ->lockForUpdate()
                    ->first();

                if (! $payment instanceof SubscriptionPayment || $payment->status->isTerminal()) {
                    return;
                }

                $payment->markNeedsReconciliation(
                    'processing_exhausted',
                    Str::limit($exception?->getMessage() ?? 'Subscription payment processing exhausted its retries.', 1_000),
                );
            });
        });
    }

    private function context(SubscriptionPayment $payment): RequestJobContext
    {
        return new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'subscription_payment',
            idempotencyKey: $payment->idempotency_key,
            metadata: [
                SubscriptionContextKeys::SUBSCRIPTION_ID => $payment->subscription_id,
                SubscriptionContextKeys::PAYMENT_ID => $payment->id,
            ],
        );
    }
}
