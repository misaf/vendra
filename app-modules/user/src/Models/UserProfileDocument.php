<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Models\TenantWithMedia;
use App\Models\User\Enums\UserProfileDocumentStatusEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class UserProfileDocument extends TenantWithMedia implements
    Contracts\BelongsToUser,
    Contracts\BelongsToUserProfile
{
    use SoftDeletes;
    use TraitBelongsToThrough;
    use Traits\BelongsToUserProfile;
    use Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'              => 'integer',
        'user_profile_id' => 'integer',
        'status'          => UserProfileDocumentStatusEnum::class,
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
