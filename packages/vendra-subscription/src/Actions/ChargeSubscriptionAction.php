<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Contracts\SubscriptionCharger;
use Misaf\VendraSupport\Data\SubscriptionCharge;

final class ChargeSubscriptionAction
{
    public function __construct(
        private readonly SubscriptionCharger $subscriptionCharger,
        private readonly ApplySubscriptionPaymentResultAction $applySubscriptionPaymentResultAction,
    ) {}

    /**
     * Process one durable payment operation without retaining its claim lock
     * during provider I/O, then apply the provider lifecycle result. A payment
     * reaching the Paid state raises SubscriptionPaymentPaid so consumers can
     * react (e.g. activating the subscriber) outside the payment engine.
     */
    public function execute(SubscriptionPayment $payment): void
    {
        if ($payment->status === SubscriptionPaymentStatus::Paid) {
            SubscriptionPaymentPaid::dispatch($payment);

            return;
        }

        if ($payment->status->isTerminal()) {
            return;
        }

        if ($payment->next_retry_at !== null && $payment->next_retry_at->isFuture()) {
            return;
        }

        if (! $this->subscriptionCharger->available()) {
            throw SubscriptionPaymentException::providerUnavailable();
        }

        if ($payment->provider !== $this->subscriptionCharger->provider()) {
            throw new LogicException("Subscription payment [{$payment->id}] belongs to provider [{$payment->provider}], not [{$this->subscriptionCharger->provider()}].");
        }

        $payment = DB::transaction(function () use ($payment): SubscriptionPayment {
            $lockedPayment = SubscriptionPayment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status->isTerminal()
                || ($lockedPayment->next_retry_at !== null && $lockedPayment->next_retry_at->isFuture())) {
                return $lockedPayment;
            }

            $lockedPayment->beginProcessing();

            return $lockedPayment;
        });

        if ($payment->status === SubscriptionPaymentStatus::Paid) {
            SubscriptionPaymentPaid::dispatch($payment);

            return;
        }

        if ($payment->status->isTerminal()
            || ($payment->next_retry_at !== null && $payment->next_retry_at->isFuture())) {
            return;
        }

        if (! app()->runningUnitTests() && DB::transactionLevel() !== 0) {
            throw new LogicException('Subscription providers must be called outside database transactions.');
        }

        $charge = new SubscriptionCharge(
            payer: $payment->payer()->firstOrFail(),
            amount: $payment->amount,
            currencyCode: $payment->currency_code,
            reference: $payment->idempotency_key,
            providerReference: $payment->provider_reference,
        );

        $result = $payment->provider_reference === null
            ? $this->subscriptionCharger->charge($charge)
            : $this->subscriptionCharger->retrieve($charge);
        $payment = $this->applySubscriptionPaymentResultAction->execute($payment, $result);

        if ($payment->status === SubscriptionPaymentStatus::Paid) {
            SubscriptionPaymentPaid::dispatch($payment);
        }
    }
}
