<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\User;
use Termehsoft\User\Observers\UserProfileObserver;

#[ObservedBy([UserProfileObserver::class])]
final class UserProfile extends BaseModelWithMedia implements
    User\Contracts\BelongsToUser,
    User\Contracts\HasUserProfileBalance,
    User\Contracts\HasUserProfileDocument,
    User\Contracts\HasUserProfilePhone
{
    use SoftDeletes;
    use User\Traits\BelongsToUser;
    use User\Traits\HasUserProfileBalance;
    use User\Traits\HasUserProfileDocument;
    use User\Traits\HasUserProfilePhone;

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
        return new Attribute(
            get: fn($value, $attributes): string => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
