<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Misaf\VendraAddress\Database\Factories\AddressFactory;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Models\UserProfile;

#[Fillable([
    'user_profile_id',
    'type',
    'label',
    'recipient_name',
    'organization',
    'line_one',
    'line_two',
    'line_three',
    'locality',
    'administrative_area',
    'postal_code',
    'sorting_code',
    'country_code',
    'locale',
    'metadata',
    'notes',
    'is_primary',
    'verified_at',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(AddressFactory::class)]
final class Address extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'type' => 'other',
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
            'recipient_name' => 'string',
            'organization' => 'string',
            'line_one' => 'string',
            'line_two' => 'string',
            'line_three' => 'string',
            'locality' => 'string',
            'administrative_area' => 'string',
            'postal_code' => 'string',
            'sorting_code' => 'string',
            'country_code' => 'string',
            'locale' => 'string',
            'metadata' => 'array',
            'notes' => 'string',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
