<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraSubscription\Database\Factories\SubscriptionPaymentFactory;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;

/**
 * @property int $id
 * @property int $subscription_id
 * @property string $payer_type
 * @property int $payer_id
 * @property string $provider
 * @property string $idempotency_key
 * @property string|null $provider_reference
 * @property int $amount
 * @property string $currency_code
 * @property SubscriptionPaymentStatus $status
 * @property int $attempt_count
 * @property string|null $failure_code
 * @property string|null $failure_message
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $processing_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $next_retry_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['subscription_id', 'payer_type', 'payer_id', 'provider', 'idempotency_key', 'provider_reference', 'amount', 'currency_code', 'status', 'attempt_count', 'failure_code', 'failure_message', 'metadata', 'processing_at', 'paid_at', 'failed_at', 'next_retry_at'])]
#[UseFactory(SubscriptionPaymentFactory::class)]
final class SubscriptionPayment extends Model implements ShouldLogActivity
{
    /** @use HasFactory<SubscriptionPaymentFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => SubscriptionPaymentStatus::Pending->value,
        'attempt_count' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscription_id' => 'integer',
            'payer_id' => 'integer',
            'amount' => 'integer',
            'status' => SubscriptionPaymentStatus::class,
            'attempt_count' => 'integer',
            'metadata' => 'array',
            'processing_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    /**
     * Payments still being collected, which a cancellation must stop.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function open(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SubscriptionPaymentStatus::Pending,
            SubscriptionPaymentStatus::Processing,
            SubscriptionPaymentStatus::RequiresAction,
            SubscriptionPaymentStatus::NeedsReconciliation,
        ]);
    }

    /**
     * Payments an operator has to look at: awaiting customer action, unreconciled, or a failed refund.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function needingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SubscriptionPaymentStatus::RequiresAction,
            SubscriptionPaymentStatus::NeedsReconciliation,
            SubscriptionPaymentStatus::RefundFailed,
        ]);
    }

    /**
     * Payments recovery should requeue: an unfinished charge whose retry is due, or a paid one awaiting activation.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function dueForRecovery(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where(function (Builder $query): void {
                    $query
                        ->whereIn('status', [
                            SubscriptionPaymentStatus::Pending,
                            SubscriptionPaymentStatus::Processing,
                            SubscriptionPaymentStatus::NeedsReconciliation,
                        ])
                        ->where(function (Builder $query): void {
                            $query
                                ->whereNull('next_retry_at')
                                ->orWhere('next_retry_at', '<=', now());
                        });
                })
                ->orWhere(fn (Builder $query): Builder => $query->awaitingActivation());
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function needsReconciliation(Builder $query): Builder
    {
        return $query->where('status', SubscriptionPaymentStatus::NeedsReconciliation);
    }

    /**
     * Payments that started processing at or before the threshold and never finished.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function stalledProcessing(Builder $query, DateTimeInterface $threshold): Builder
    {
        return $query
            ->where('status', SubscriptionPaymentStatus::Processing)
            ->where('processing_at', '<=', $threshold);
    }

    /**
     * Payments paid within the given moments, both inclusive.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function paidBetween(Builder $query, DateTimeInterface $from, DateTimeInterface $until): Builder
    {
        return $query
            ->where('status', SubscriptionPaymentStatus::Paid)
            ->whereBetween('paid_at', [$from, $until]);
    }

    /**
     * Paid payments whose subscription was never activated.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function awaitingActivation(Builder $query): Builder
    {
        return $query
            ->where('status', SubscriptionPaymentStatus::Paid)
            ->whereHas('subscription', fn (Builder $query): Builder => $query->where('status', SubscriptionStatus::PendingPayment));
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function payer(): MorphTo
    {
        return $this->morphTo();
    }

    public function beginProcessing(): bool
    {
        return $this->forceFill([
            'status' => SubscriptionPaymentStatus::Processing,
            'attempt_count' => $this->attempt_count + 1,
            'processing_at' => now(),
            'next_retry_at' => null,
            'failure_code' => null,
            'failure_message' => null,
        ])->save();
    }

    public function recordProviderResult(
        SubscriptionPaymentStatus $status,
        ?string $providerReference,
        ?string $failureCode,
        ?string $failureMessage,
    ): bool {
        return $this->forceFill([
            'status' => $status,
            'provider_reference' => $this->provider_reference ?? $providerReference,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'paid_at' => $status === SubscriptionPaymentStatus::Paid ? ($this->paid_at ?? now()) : $this->paid_at,
            'failed_at' => $status === SubscriptionPaymentStatus::Failed ? ($this->failed_at ?? now()) : $this->failed_at,
            'next_retry_at' => $status === SubscriptionPaymentStatus::Processing ? now()->addMinutes(5) : null,
        ])->save();
    }

    public function markNeedsReconciliation(string $failureCode, string $failureMessage): bool
    {
        return $this->forceFill([
            'status' => SubscriptionPaymentStatus::NeedsReconciliation,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'next_retry_at' => now()->addMinutes(15),
        ])->save();
    }

    public function cancel(): bool
    {
        return $this->update(['status' => SubscriptionPaymentStatus::Cancelled]);
    }
}
