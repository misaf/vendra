<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSupport\Contracts\SubscriptionCharger;
use Misaf\VendraSupport\Data\SubscriptionCharge;

final readonly class ChargeSubscriptionAction
{
    public function __construct(
        private SubscriptionCharger $subscriptionCharger,
        private ApplySubscriptionPaymentResultAction $applySubscriptionPaymentResultAction,
    ) {}

    /**
     * The claim lock is released during provider I/O.
     */
    public function execute(SubscriptionPayment $payment): void
    {
        if ($payment->status === SubscriptionPaymentStatus::Paid) {
            event(new SubscriptionPaymentPaid($payment));

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
            $lockedPayment = $payment->refreshForUpdate();

            if ($lockedPayment->status->isTerminal()
                || ($lockedPayment->next_retry_at !== null && $lockedPayment->next_retry_at->isFuture())) {
                return $lockedPayment;
            }

            // A payment outliving its cancelled subscription must never reach the provider.
            if ($lockedPayment->subscription()->withTrashed()->first()?->status === SubscriptionStatus::Cancelled) {
                $lockedPayment->cancel();

                return $lockedPayment;
            }

            $lockedPayment->beginProcessing();

            return $lockedPayment;
        });

        if ($payment->status === SubscriptionPaymentStatus::Paid) {
            event(new SubscriptionPaymentPaid($payment));

            return;
        }

        if ($payment->status->isTerminal()
            || ($payment->next_retry_at !== null && $payment->next_retry_at->isFuture())) {
            return;
        }

        throw_if(! app()->runningUnitTests() && DB::transactionLevel() !== 0, LogicException::class, 'Subscription providers must be called outside database transactions.');

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
            event(new SubscriptionPaymentPaid($payment));
        }
    }
}
