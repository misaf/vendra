<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $email
 * @property string|null $name
 * @property string $unsubscribe_token
 * @property Carbon|null $subscribed_at
 * @property Carbon|null $unsubscribed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['email', 'name', 'subscribed_at', 'unsubscribed_at'])]
#[Hidden(['tenant_id', 'unsubscribe_token'])]
#[UseFactory(NewsletterSubscriberFactory::class)]
final class NewsletterSubscriber extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    use SoftDeletes;

    protected static function booted(): void
    {
        self::creating(function (NewsletterSubscriber $subscriber): void {
            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(48);
            }

            if ($subscriber->subscribed_at === null && $subscriber->unsubscribed_at === null) {
                $subscriber->subscribed_at = now();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function isSubscribed(): bool
    {
        return $this->unsubscribed_at === null;
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    #[Scope]
    protected function subscribed(Builder $query): Builder
    {
        return $query->whereNull('unsubscribed_at');
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    #[Scope]
    protected function unsubscribed(Builder $query): Builder
    {
        return $query->whereNotNull('unsubscribed_at');
    }
}
