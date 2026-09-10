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
use Misaf\VendraNewsletter\Database\Factories\NewsletterFactory;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $subject
 * @property string|null $content
 * @property NewsletterStatusEnum $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['subject', 'content', 'status', 'scheduled_at', 'sent_at'])]
#[Hidden(['tenant_id'])]
#[UseFactory(NewsletterFactory::class)]
final class Newsletter extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<NewsletterFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'status' => NewsletterStatusEnum::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    #[Scope]
    protected function due(Builder $query, ?Carbon $now = null): Builder
    {
        return $query
            ->where('status', NewsletterStatusEnum::Scheduled->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now ?? now());
    }
}
