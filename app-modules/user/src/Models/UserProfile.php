<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Termehsoft\Tenant\Models\TenantWithMedia;

final class UserProfile extends TenantWithMedia implements
    Contracts\BelongsToUser,
    Contracts\HasUserProfileBalance,
    Contracts\HasUserProfileDocument,
    Contracts\HasUserProfilePhone
{
    use Eloquent\SoftDeletes;
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
        return new Attribute(
            get: fn($value, $attributes): string => "{$attributes['first_name']} {$attributes['last_name']}",
        );
    }
}
