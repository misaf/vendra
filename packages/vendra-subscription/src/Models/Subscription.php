<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraSubscription\Database\Factories\SubscriptionFactory;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;

/**
 * @property int $id
 * @property string $subscriber_type
 * @property int $subscriber_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property int $price
 * @property string|null $currency_code
 * @property Carbon|null $trial_ends_at
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $expiry_reminder_sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['subscriber_type', 'subscriber_id', 'plan_id', 'status', 'price', 'currency_code', 'trial_ends_at', 'starts_at', 'ends_at', 'expiry_reminder_sent_at'])]
#[Hidden(['active_subscriber_guard'])]
#[UseFactory(SubscriptionFactory::class)]
final class Subscription extends Model implements ShouldLogActivity
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'subscriber_type' => 'string',
            'subscriber_id' => 'integer',
            'plan_id' => 'integer',
            'status' => SubscriptionStatus::class,
            'price' => 'integer',
            'currency_code' => 'string',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'expiry_reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<SubscriptionPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active)
            ->where('starts_at', '<=', now())
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function lapsed(Builder $query): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function endingWithin(Builder $query, int $days): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function expiringWithin(Builder $query, int $days): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active)
            ->whereNull('expiry_reminder_sent_at')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }

    /**
     * Get the date the subscription's units are suspended, or null if it never expires.
     */
    public function suspendAt(): ?Carbon
    {
        $plan = $this->plan;

        if ($this->ends_at === null || $plan === null) {
            return null;
        }

        return $plan->resolveSuspendDate($this->ends_at);
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription is active by status and period.
     */
    public function isActive(): bool
    {
        if ($this->status !== SubscriptionStatus::Active) {
            return false;
        }

        if ($this->starts_at->isFuture()) {
            return false;
        }

        return $this->ends_at === null || $this->ends_at->isFuture();
    }

    /**
     * Determine if the subscription is still open to cancellation.
     *
     * The console buttons and the domain actions share these predicates, so
     * nothing can do more than the console offers.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, SubscriptionStatus::cancellable(), true);
    }

    public function canBeReactivated(): bool
    {
        return in_array($this->status, [SubscriptionStatus::Cancelled, SubscriptionStatus::Expired, SubscriptionStatus::PastDue], true);
    }

    /**
     * @phpstan-assert-if-true !null $this->ends_at
     */
    public function canBeExtended(): bool
    {
        return $this->status === SubscriptionStatus::Active && $this->ends_at !== null;
    }

    public function activate(): bool
    {
        return $this->update(['status' => SubscriptionStatus::Active]);
    }

    public function markPastDue(): bool
    {
        return $this->update(['status' => SubscriptionStatus::PastDue]);
    }

    public function expire(): bool
    {
        return $this->update(['status' => SubscriptionStatus::Expired]);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => SubscriptionStatus::Cancelled]);
    }
}
