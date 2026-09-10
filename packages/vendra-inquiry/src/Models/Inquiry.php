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
use Misaf\VendraInquiry\Enums\InquiryStatusEnum;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * Someone writing in from the storefront.
 *
 * An enquiry is what a customer said, captured verbatim: the studio answers it
 * by hand. It is deliberately not a ticketing system — there are no threads,
 * assignees, or SLAs here.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $occasion
 * @property string $message
 * @property InquiryStatusEnum $status
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

    use SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => InquiryStatusEnum::New->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'status' => InquiryStatusEnum::class,
            'metadata' => 'array',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * Record that a person has written back.
     */
    public function markAnswered(): void
    {
        $this->forceFill([
            'status' => InquiryStatusEnum::Answered,
            'answered_at' => now(),
        ])->save();
    }

    public function close(): void
    {
        $this->forceFill(['status' => InquiryStatusEnum::Closed])->save();
    }

    public function reopen(): void
    {
        $this->forceFill([
            'status' => InquiryStatusEnum::New,
            'answered_at' => null,
        ])->save();
    }

    /**
     * @param  Builder<self>  $builder
     */
    #[Scope]
    protected function unanswered(Builder $builder): void
    {
        $builder->where('status', InquiryStatusEnum::New);
    }
}
