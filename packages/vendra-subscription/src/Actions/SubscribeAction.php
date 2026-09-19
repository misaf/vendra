<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Misaf\VendraSubscription\Context\SubscriptionContextKeys;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Contracts\SubscriptionUnitSuspender;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionActivated;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Jobs\ProcessSubscriptionPayment;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSubscription\Support\SubscriptionRegistry;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\SubscriptionCharger;

final readonly class SubscribeAction
{
    public function __construct(
        private SubscriptionCharger $subscriptionCharger,
        private SubscriptionRegistry $subscriptionRegistry,
        private SubscriptionUnitSuspender $unitSuspender,
    ) {}

    /**
     * Paid periods stay pending until their payment succeeds, leaving current
     * access in place.
     *
     * @param  Model&SubscriptionSubscriber  $subscriber
     *
     * @throws SubscriptionLimitException
     */
    public function execute(SubscriptionSubscriber $subscriber, Plan $plan, ?Carbon $startsAt = null): Subscription
    {
        if ($plan->price > 0 && $plan->currency_code === null) {
            throw SubscriptionPaymentException::missingCurrency($plan);
        }

        $startsAt ??= Date::now();

        [
            'subscription' => $subscription,
            'payment' => $payment,
        ] = DB::transaction(
            /** @return array{subscription: Subscription, payment: SubscriptionPayment|null} */
            function () use ($subscriber, $plan, $startsAt): array {
                $lockedSubscriber = $this->subscriptionRegistry->lockSubscriber($subscriber);

                $currentUnits = $lockedSubscriber->subscribedUnitCount();

                if ($currentUnits > $plan->max_units) {
                    throw SubscriptionLimitException::planBelowUsage($lockedSubscriber, $plan->max_units, $currentUnits);
                }

                $openPayments = $this->subscriptionRegistry->lockOpenPayments($lockedSubscriber);

                if ($openPayments->contains(fn (SubscriptionPayment $payment): bool => $payment->status !== SubscriptionPaymentStatus::Pending)) {
                    throw SubscriptionPaymentException::paymentInProgress();
                }

                if ($openPayments->isNotEmpty()) {
                    $openPayments->each(
                        function (SubscriptionPayment $payment): void {
                            $payment->cancel();
                        },
                    );
                    $this->subscriptionRegistry->cancelPending(
                        $lockedSubscriber,
                        $openPayments->map(fn (SubscriptionPayment $payment): int => $payment->subscription_id)->all(),
                    );
                }

                // A trial only applies to the subscriber's very first subscription.
                $trialEndsAt = $plan->hasTrial() && ! $lockedSubscriber->hasSubscriptions()
                    ? $startsAt->copy()->addDays($plan->trial_days)
                    : null;
                $requiresCollection = $plan->price > 0;
                $requiresImmediatePayment = $requiresCollection && $trialEndsAt === null;

                if (! $requiresImmediatePayment) {
                    $this->subscriptionRegistry->cancelActive($lockedSubscriber);
                }

                $subscription = new Subscription([
                    'plan_id' => $plan->getKey(),
                    'status' => $requiresImmediatePayment ? SubscriptionStatus::PendingPayment : SubscriptionStatus::Active,
                    'price' => $plan->price,
                    'currency_code' => $plan->currency_code,
                    'trial_ends_at' => $trialEndsAt,
                    'starts_at' => $startsAt,
                    'ends_at' => $plan->resolveEndDate($startsAt),
                ]);
                $subscription->subscriber()->associate($lockedSubscriber);
                $subscription->save();

                if (! $requiresImmediatePayment) {
                    $this->unitSuspender->reactivateSuspendedUnits($lockedSubscriber);

                    if (! $requiresCollection) {
                        return ['subscription' => $subscription, 'payment' => null];
                    }
                }

                if (! $this->subscriptionCharger->available()) {
                    throw SubscriptionPaymentException::providerUnavailable();
                }

                $payer = $lockedSubscriber->subscriptionPayer();

                if ($payer === null) {
                    throw SubscriptionPaymentException::missingPayer($subscription);
                }

                $payment = $subscription->payments()->make([
                    'provider' => $this->subscriptionCharger->provider(),
                    'idempotency_key' => (string) Str::uuid(),
                    'amount' => $subscription->price,
                    'currency_code' => $subscription->currency_code,
                    'next_retry_at' => $trialEndsAt,
                ]);
                $payment->payer()->associate($payer);
                $payment->save();

                return ['subscription' => $subscription, 'payment' => $payment];
            }, attempts: 5);

        new RequestJobContext(
            traceId: RequestJobContext::resolveTraceId(),
            operation: 'subscription_create',
            idempotencyKey: $payment?->idempotency_key,
            metadata: [
                SubscriptionContextKeys::SUBSCRIPTION_ID => $subscription->id,
                SubscriptionContextKeys::PAYMENT_ID => $payment?->id,
            ],
        )->scope(function () use ($payment, $subscription): void {
            if ($payment instanceof SubscriptionPayment && $payment->next_retry_at === null) {
                dispatch(new ProcessSubscriptionPayment($payment->id))->afterCommit();
            }

            if ($subscription->status === SubscriptionStatus::Active) {
                event(new SubscriptionActivated($subscription));
            }
        });

        return $subscription;
    }
}
