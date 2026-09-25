<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Contracts\BillingProfile;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Events\SubscriptionInvoiceIssued;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionInvoice;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

final readonly class IssueSubscriptionInvoiceAction
{
    private const string NUMBER_PREFIX = 'INV';

    public function __construct(private BillingProfile $billingProfile) {}

    /**
     * Numbers run without gaps within the year the payment was collected: the
     * year's sequence row is locked for the whole transaction, so concurrent
     * issues queue behind it instead of skipping or reusing a number.
     */
    public function execute(SubscriptionPayment $payment): SubscriptionInvoice
    {
        throw_if($payment->paid_at === null, LogicException::class, "Subscription payment [{$payment->id}] has not been paid.");

        return DB::transaction(function () use ($payment): SubscriptionInvoice {
            $existing = SubscriptionInvoice::query()->where('subscription_payment_id', $payment->id)->lockForUpdate()->first();

            if ($existing instanceof SubscriptionInvoice) {
                return $existing;
            }

            $subscription = $payment->subscription()->with(['plan', 'subscriber'])->firstOrFail();
            $subscriber = $subscription->subscriber;

            throw_unless($subscriber instanceof SubscriptionSubscriber, LogicException::class, "Subscription [{$subscription->id}] has unsupported subscriber type [{$subscription->subscriber_type}].");

            $issuedAt = Date::now();

            $invoice = SubscriptionInvoice::query()->create([
                'subscription_payment_id' => $payment->id,
                'subscriber_type' => $subscription->subscriber_type,
                'subscriber_id' => $subscription->subscriber_id,
                'number' => $this->nextNumber($payment->paid_at->year),
                'issued_at' => $issuedAt,
                'currency_code' => $payment->currency_code,
                'net_amount' => $payment->net_amount,
                'tax_rate' => $payment->tax_rate,
                'tax_label' => $this->billingProfile->taxLabel(),
                'tax_amount' => $payment->tax_amount,
                'total_amount' => $payment->amount,
                'seller' => $this->billingProfile->seller(),
                'buyer' => $subscriber->billingDetails(),
                'lines' => [self::line($subscription, $payment)],
            ]);

            event(new SubscriptionInvoiceIssued($invoice));

            return $invoice;
        });
    }

    private function nextNumber(int $year): string
    {
        $now = Date::now();

        DB::table('subscription_invoice_sequences')->insertOrIgnore([
            'year' => $year,
            'last_number' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $lastNumber = DB::table('subscription_invoice_sequences')->where('year', $year)->lockForUpdate()->value('last_number');

        throw_unless(is_numeric($lastNumber), LogicException::class, "Invoice sequence for [{$year}] could not be read.");

        $number = (int) $lastNumber + 1;

        DB::table('subscription_invoice_sequences')->where('year', $year)->update(['last_number' => $number, 'updated_at' => $now]);

        return sprintf('%s-%d-%06d', self::NUMBER_PREFIX, $year, $number);
    }

    /**
     * @return array{description: string, period_start: string|null, period_end: string|null, prorated: bool, amount: int}
     */
    private static function line(Subscription $subscription, SubscriptionPayment $payment): array
    {
        return [
            'description' => $subscription->plan->name ?? '',
            'period_start' => $subscription->starts_at->toDateString(),
            'period_end' => $subscription->ends_at?->toDateString(),
            'prorated' => $payment->net_amount < $subscription->price,
            'amount' => $payment->net_amount,
        ];
    }
}
