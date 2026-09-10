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
     * The subscriber that owns this subscription — any model, resolved
     * polymorphically via `subscriber_type`/`subscriber_id`. The package stays
     * agnostic of concrete subscriber classes; the `(subscriber_type,
     * active_subscriber_guard)` unique index enforces one active subscription
     * per subscriber, keyed by type so different subscriber models never
     * conflate ids.
     *
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
     * Limit the query to subscriptions that are active right now.
     *
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
     * Limit the query to active subscriptions whose period has already lapsed.
     *
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
     * Limit the query to active subscriptions expiring within the given number
     * of days that have not yet had an expiry reminder sent.
     *
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
     * The moment this subscription's units should be suspended, taking the
     * plan's grace window into account. Null when it never expires.
     */
    public function suspendAt(): ?Carbon
    {
        $plan = $this->plan;

        if ($this->ends_at === null || $plan === null) {
            return null;
        }

        return $plan->resolveSuspendDate($this->ends_at);
    }

    /**
     * Whether this subscription is currently within its trial period.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Whether this subscription is active right now (status and period).
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
