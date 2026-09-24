<?php

declare(strict_types=1);

namespace Misaf\VendraVerification\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Traits\BelongsToUserProfile;
use Misaf\VendraVerification\Database\Factories\VerificationFactory;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_profile_id
 * @property string $type
 * @property string $status
 * @property string|null $provider
 * @property string|null $reference
 * @property string|null $country_code
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $verified_at
 * @property Carbon|null $expires_at
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
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
    use BelongsToUserProfile;

    /** @use HasFactory<VerificationFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

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
