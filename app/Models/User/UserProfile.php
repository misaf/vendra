<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy(TenantScope::class)]
final class UserProfile extends Model implements
    HasMedia,
    Contracts\BelongsToUser
{
    use ActivityLog;

    use BelongsToTenant;

    use HasFactory;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

    use Traits\BelongsToUser;

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'first_name'  => 'string',
        'last_name'   => 'string',
        'description' => 'string',
        'birthdate'   => 'datetime:Y-m-d',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'description',
        'birthdate',
        'status',
    ];

    /**
     * Get the balances for the user profile.
     *
     * @return HasMany
     */
    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(UserProfileBalance::class);
    }

    /**
     * Get the latest document for the user profile.
     *
     * @return HasOne
     */
    public function userProfileDocument(): HasOne
    {
        return $this->hasOne(UserProfileDocument::class)->latestOfMany();
    }

    /**
     * Get all documents for the user profile.
     *
     * @return HasMany
     */
    public function userProfileDocuments(): HasMany
    {
        return $this->hasMany(UserProfileDocument::class);
    }

    /**
     * Get the latest phone for the user profile.
     *
     * @return HasOne
     */
    public function userProfilePhone(): HasOne
    {
        return $this->hasOne(UserProfilePhone::class)->latestOfMany();
    }

    /**
     * Get all phones for the user profile.
     *
     * @return HasMany
     */
    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(UserProfilePhone::class);
    }

    /**
     * Get the full name of the user.
     *
     * @return Attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
