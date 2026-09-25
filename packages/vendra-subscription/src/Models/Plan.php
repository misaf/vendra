<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Misaf\VendraSubscription\Database\Factories\PlanFactory;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Observers\PlanObserver;
use Misaf\VendraSubscription\Support\MoneyFormatter;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

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
 * @property array<string, int>|null $limits
 * @property bool $active
 * @property bool $is_default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'slug', 'description', 'max_units', 'period_unit', 'period_count', 'grace_days', 'price', 'currency_code', 'trial_days', 'features', 'limits', 'active', 'is_default'])]
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
            'limits' => 'array',
            'active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function inactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function default(Builder $query): Builder
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

    public function resolveEndDate(Carbon $start): Carbon
    {
        return Date::instance($this->period_unit->advance($start, $this->period_count));
    }

    public function isFree(): bool
    {
        return $this->price === 0;
    }

    /**
     * Format the plan's price for display, such as `$29.00`.
     */
    public function formattedPrice(): string
    {
        return MoneyFormatter::format($this->price, $this->currency_code);
    }

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }

    public function allows(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    /**
     * Get the limit for the key, or null when the plan leaves it unlimited.
     */
    public function limit(string $key): ?int
    {
        $limit = $this->limits[$key] ?? null;

        return $limit === null ? null : (int) $limit;
    }

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
