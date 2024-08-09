<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Termehsoft\User;
use Termehsoft\User\Observers\UserProfilePhoneObserver;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ObservedBy([UserProfilePhoneObserver::class])]
final class UserProfilePhone extends BaseModel implements
    User\Contracts\BelongsToUserProfile,
    User\Contracts\BelongsToUser
{
    use SoftDeletes;
    use TraitBelongsToThrough;
    use User\Traits\BelongsToUserProfile;
    use User\Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'               => 'integer',
        'user_profile_id'  => 'integer',
        'country'          => 'string',
        'phone'            => RawPhoneNumberCast::class . ':country',
        'phone_normalized' => 'string',
        'phone_national'   => 'string',
        'phone_e164'       => 'string',
        'status'           => User\Enums\UserProfileDocumentStatusEnum::class,
        'verified_at'      => 'datetime',
        'created_at'       => DateCast::class,
        'updated_at'       => DateCast::class,
        'deleted_at'       => DateCast::class,
    ];

    protected $fillable = [
        'user_profile_id',
        'country',
        'phone',
        'phone_normalized',
        'phone_national',
        'phone_e164',
        'status',
        'verified_at',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (UserProfilePhone $userProfilePhone): void {
            if ($userProfilePhone->isDirty('phone') && $userProfilePhone->phone) {
                $userProfilePhone->phone_normalized = User\Services\UserProfilePhoneService::phoneNormalized($userProfilePhone->phone);
                $userProfilePhone->phone_national = User\Services\UserProfilePhoneService::phoneNational($userProfilePhone->country, $userProfilePhone->phone);
                $userProfilePhone->phone_e164 = User\Services\UserProfilePhoneService::phoneE164($userProfilePhone->country, $userProfilePhone->phone);
            }

            if ($userProfilePhone->isDirty('status') === User\Enums\UserProfilePhoneStatusEnum::Approved->value) {
                $userProfilePhone->verified_at = now();
            }
        });
    }
}
