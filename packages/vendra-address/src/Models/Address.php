<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraAddress\Database\Factories\AddressFactory;
use Misaf\VendraAddress\Observers\AddressObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraUserProfile\Traits\BelongsToUserProfile;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_profile_id
 * @property string $type
 * @property string|null $label
 * @property string|null $recipient_name
 * @property string|null $organization
 * @property string $line_one
 * @property string|null $line_two
 * @property string|null $line_three
 * @property string|null $locality
 * @property string|null $administrative_area
 * @property string|null $postal_code
 * @property string|null $sorting_code
 * @property string $country_code
 * @property string|null $locale
 * @property array<string, mixed>|null $metadata
 * @property string|null $notes
 * @property bool $is_default
 * @property Carbon|null $verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
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
    'is_default',
    'verified_at',
])]
#[Hidden(['tenant_id'])]
#[ObservedBy([AddressObserver::class])]
#[UseFactory(AddressFactory::class)]
final class Address extends Model implements ShouldLogActivity
{
    use BelongsToTenant;
    use BelongsToUserProfile;

    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $attributes = [
        'type' => 'other',
        'is_default' => false,
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
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
