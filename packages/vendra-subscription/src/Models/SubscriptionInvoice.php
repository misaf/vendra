<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraSubscription\Database\Factories\SubscriptionInvoiceFactory;
use Misaf\VendraSubscription\Support\MoneyFormatter;

/**
 * An issued invoice never changes: it snapshots the seller, the buyer and the
 * charge, so it renders the same however settings or plans change later.
 *
 * @property int $id
 * @property int $subscription_payment_id
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property string $number
 * @property Carbon $issued_at
 * @property string $currency_code
 * @property int $net_amount
 * @property int $tax_rate
 * @property string $tax_label
 * @property int $tax_amount
 * @property int $total_amount
 * @property array{name: string, address: string|null, tax_id: string|null} $seller
 * @property array{name: string, email: string|null, address: string|null, tax_id: string|null} $buyer
 * @property list<array{description: string, period_start: string|null, period_end: string|null, prorated: bool, amount: int}> $lines
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['subscription_payment_id', 'subscriber_type', 'subscriber_id', 'number', 'issued_at', 'currency_code', 'net_amount', 'tax_rate', 'tax_label', 'tax_amount', 'total_amount', 'seller', 'buyer', 'lines'])]
#[UseFactory(SubscriptionInvoiceFactory::class)]
final class SubscriptionInvoice extends Model
{
    /** @use HasFactory<SubscriptionInvoiceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscription_payment_id' => 'integer',
            'subscriber_id' => 'integer',
            'issued_at' => 'datetime',
            'net_amount' => 'integer',
            'tax_rate' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'seller' => 'array',
            'buyer' => 'array',
            'lines' => 'array',
        ];
    }

    /**
     * @return BelongsTo<SubscriptionPayment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class, 'subscription_payment_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    public function formattedTotal(): string
    {
        return MoneyFormatter::format($this->total_amount, $this->currency_code);
    }

    public function formattedAmount(int $amount): string
    {
        return MoneyFormatter::format($amount, $this->currency_code);
    }

    /**
     * The tax rate as a percentage, such as "19" or "7.5".
     */
    public function taxPercentage(): string
    {
        return rtrim(rtrim(number_format($this->tax_rate / 100, 2, '.', ''), '0'), '.');
    }

    public function downloadName(): string
    {
        return "{$this->number}.pdf";
    }
}
