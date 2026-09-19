<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraInquiry\Database\Factories\InquiryFactory;
use Misaf\VendraInquiry\States\Answered;
use Misaf\VendraInquiry\States\Closed;
use Misaf\VendraInquiry\States\InquiryState;
use Misaf\VendraInquiry\States\Open;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $occasion
 * @property string $message
 * @property InquiryState $status
 * @property string|null $source
 * @property string|null $locale
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $answered_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'name',
    'email',
    'phone',
    'occasion',
    'message',
    'status',
    'source',
    'locale',
    'metadata',
    'answered_at',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(InquiryFactory::class)]
final class Inquiry extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    use HasStates;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'status' => InquiryState::class,
            'metadata' => 'array',
            'answered_at' => 'datetime',
        ];
    }

    public function markAnswered(): void
    {
        $this->status->transitionTo(Answered::class);
    }

    public function close(): void
    {
        $this->status->transitionTo(Closed::class);
    }

    public function reopen(): void
    {
        $this->status->transitionTo(Open::class);
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function unanswered(Builder $builder): void
    {
        $builder->whereState('status', Open::class);
    }
}
