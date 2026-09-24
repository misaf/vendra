<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraPhone\Database\Factories\PhoneNumberFactory;
use Misaf\VendraPhone\Observers\PhoneNumberObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Traits\BelongsToUserProfile;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_profile_id
 * @property string $type
 * @property string|null $label
 * @property string $country_code
 * @property string $number
 * @property string|null $extension
 * @property array<string, mixed>|null $metadata
 * @property string|null $notes
 * @property bool $is_primary
 * @property Carbon|null $verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'user_profile_id',
    'type',
    'label',
    'country_code',
    'number',
    'extension',
    'metadata',
    'notes',
    'is_primary',
    'verified_at',
])]
#[Hidden(['tenant_id', 'primary_profile_guard'])]
#[ObservedBy([PhoneNumberObserver::class])]
#[UseFactory(PhoneNumberFactory::class)]
final class PhoneNumber extends Model implements ShouldLogActivity
{
    use BelongsToTenant;
    use BelongsToUserProfile;

    /** @use HasFactory<PhoneNumberFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'type' => 'mobile',
        'is_primary' => false,
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'user_profile_id' => 'integer',
            'type' => 'string',
            'label' => 'string',
            'country_code' => 'string',
            'number' => 'string',
            'extension' => 'string',
            'metadata' => 'array',
            'notes' => 'string',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
