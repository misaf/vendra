<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Models;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Misaf\VendraSubscription\Database\Factories\PlanFactory;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Observers\PlanObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Throwable;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $max_units
 * @property PeriodUnit $period_unit
 * @property int $period_count
 * @property int $grace_days
 * @property int $price
 * @property string|null $currency_code
 * @property int $trial_days
 * @property list<string>|null $features
 * @property bool $active
 * @property bool $is_default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'slug', 'description', 'max_units', 'period_unit', 'period_count', 'grace_days', 'price', 'currency_code', 'trial_days', 'features', 'active', 'is_default'])]
#[Hidden(['default_guard'])]
#[ObservedBy([PlanObserver::class])]
#[UseFactory(PlanFactory::class)]
final class Plan extends Model implements ShouldLogActivity
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasSlug;
    use SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'active' => true,
        'is_default' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'max_units' => 'integer',
            'period_unit' => PeriodUnit::class,
            'period_count' => 'integer',
            'grace_days' => 'integer',
            'price' => 'integer',
            'currency_code' => 'string',
            'trial_days' => 'integer',
            'features' => 'array',
            'active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isInUse(): bool
    {
        return $this->subscriptions()->withTrashed()->exists();
    }

    /**
     * Resolve the subscription end date for a period starting at the given date.
     */
    public function resolveEndDate(Carbon $start): Carbon
    {
        return Carbon::instance($this->period_unit->advance($start, $this->period_count));
    }

    /**
     * Whether this plan has no recurring charge.
     */
    public function isFree(): bool
    {
        return $this->price === 0;
    }

    /**
     * The plan's recurring charge formatted for display, e.g. `$29.00`.
     * Falls back to a plain number and code for currencies the money
     * formatter does not recognize.
     */
    public function formattedPrice(): string
    {
        if ($this->currency_code === null) {
            return $this->formatPlainPrice();
        }

        try {
            return (new Money($this->price, $this->currency_code))->format();
        } catch (Throwable) {
            return $this->formatPlainPrice().' '.$this->currency_code;
        }
    }

    /**
     * The price rendered as a plain localized number, falling back to the raw
     * value when the formatter cannot render it.
     */
    private function formatPlainPrice(): string
    {
        return Number::format($this->price, locale: 'en') ?: (string) $this->price;
    }

    /**
     * Whether the plan offers a trial period.
     */
    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    /**
     * Whether the plan grants the given feature entitlement.
     */
    public function allows(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    /**
     * The moment a lapsed subscription should fall out of grace:
     * the period end plus the plan's grace window.
     */
    public function resolveSuspendDate(Carbon $endsAt): Carbon
    {
        return $endsAt->copy()->addDays($this->grace_days);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
