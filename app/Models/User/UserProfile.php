<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class UserProfile extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\User::class,
        );
    }

    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\User\UserProfileBalance::class,
        );
    }

    public function userProfileDocument(): HasOne
    {
        return $this->hasOne(
            related: \App\Models\User\UserProfileDocument::class,
        )->latestOfMany();
    }

    public function userProfileDocuments(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\User\UserProfileDocument::class,
        );
    }

    public function userProfilePhone(): HasOne
    {
        return $this->hasOne(
            related: \App\Models\User\UserProfilePhone::class,
        )->latestOfMany();
    }

    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\User\UserProfilePhone::class,
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
