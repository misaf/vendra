<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class UserProfile extends Tenant implements
    HasMedia,
    Contracts\BelongsToUser,
    Contracts\HasUserProfileBalance,
    Contracts\HasUserProfileDocument,
    Contracts\HasUserProfilePhone
{
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }
    use SoftDeletes;
    use Traits\BelongsToUser;
    use Traits\HasUserProfileBalance;
    use Traits\HasUserProfileDocument;
    use Traits\HasUserProfilePhone;

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
     * Get the full name of the user.
     *
     * @return Attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes): string => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
