<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraPhone\Database\Factories\PhoneNumberFactory;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Models\UserProfile;

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
#[Hidden(['tenant_id'])]
#[UseFactory(PhoneNumberFactory::class)]
final class PhoneNumber extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<PhoneNumberFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'type' => 'mobile',
        'is_primary' => false,
    ];

    /** @return BelongsTo<UserProfile, $this> */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

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
