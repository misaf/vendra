<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Tests\Fixtures;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * Stands in for the reseller so store tests exercise only the subscriber contract.
 */
#[Unguarded]
#[Table(name: 'store_test_billing_subscribers')]
final class BillingSubscriber extends Model implements SubscriptionSubscriber
{
    use HasFactory;
    use SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = ['active' => true];

    public static function createTable(): void
    {
        Schema::create('store_test_billing_subscribers', function (Blueprint $table): void {
            $table->id();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public static function withPlan(int $maxUnits, int $existingStores = 0): self
    {
        $subscriber = self::query()->create();

        Subscription::factory()
            ->forSubscriber($subscriber)
            ->for(Plan::factory()->active()->maxUnits($maxUnits))
            ->create();

        Store::factory()->count($existingStores)->create(['reseller_id' => $subscriber->getKey()]);

        return $subscriber;
    }

    /**
     * @return MorphMany<Subscription, $this>
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()->active()->latest('starts_at')->first();
    }

    public function latestSubscription(): ?Subscription
    {
        return $this->subscriptions()->latest('starts_at')->first();
    }

    public function hasSubscriptions(): bool
    {
        return $this->subscriptions()->exists();
    }

    public function canHoldUnits(): bool
    {
        return $this->active;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function notifyContact(Notification $notification): void {}

    public function subscriptionPayer(): ?Model
    {
        return null;
    }

    public function subscribedUnitCount(): int
    {
        return Store::query()->where('reseller_id', $this->getKey())->count();
    }

    public function activeSubscribedUnitCount(): int
    {
        return Store::query()->where('reseller_id', $this->getKey())->accessible()->count();
    }
}
