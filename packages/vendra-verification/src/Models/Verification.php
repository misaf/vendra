<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraVerification\Database\Factories\VerificationFactory;

#[Fillable([
    'user_profile_id',
    'type',
    'status',
    'provider',
    'reference',
    'country_code',
    'metadata',
    'verified_at',
    'expires_at',
    'notes',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(VerificationFactory::class)]
final class Verification extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<VerificationFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
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
            'status' => 'string',
            'provider' => 'string',
            'reference' => 'string',
            'country_code' => 'string',
            'metadata' => 'array',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'notes' => 'string',
        ];
    }
}
