<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\User;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class UserProfileDocument extends BaseModelWithMedia implements
    User\Contracts\BelongsToUser,
    User\Contracts\BelongsToUserProfile
{
    use SoftDeletes;
    use TraitBelongsToThrough;
    use User\Traits\BelongsToUserProfile;
    use User\Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'              => 'integer',
        'user_profile_id' => 'integer',
        'status'          => User\Enums\UserProfileDocumentStatusEnum::class,
        'verified_at'     => 'datetime',
        'created_at'      => DateCast::class,
        'updated_at'      => DateCast::class,
        'deleted_at'      => DateCast::class,
    ];

    protected $fillable = [
        'user_profile_id',
        'status',
        'verified_at',
    ];
}
