<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Models\User\Enums\UserProfileDocumentStatusEnum;
use App\Models\User\Enums\UserProfilePhoneStatusEnum;
use App\Models\User\Services\UserProfilePhoneService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class UserProfilePhone extends Tenant implements
    Contracts\BelongsToUserProfile,
    Contracts\BelongsToUser
{
    use SoftDeletes;
    use TraitBelongsToThrough;
    use Traits\BelongsToUserProfile;
    use Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'               => 'integer',
        'user_profile_id'  => 'integer',
        'country'          => 'string',
        'phone'            => RawPhoneNumberCast::class . ':country',
        'phone_normalized' => 'string',
        'phone_national'   => 'string',
        'phone_e164'       => 'string',
        'status'           => UserProfileDocumentStatusEnum::class,
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
                $userProfilePhone->phone_normalized = UserProfilePhoneService::phoneNormalized($userProfilePhone->phone);
                $userProfilePhone->phone_national = UserProfilePhoneService::phoneNational($userProfilePhone->country, $userProfilePhone->phone);
                $userProfilePhone->phone_e164 = UserProfilePhoneService::phoneE164($userProfilePhone->country, $userProfilePhone->phone);
            }

            if ($userProfilePhone->isDirty('status') === UserProfilePhoneStatusEnum::Approved->value) {
                $userProfilePhone->verified_at = now();
            }
        });
    }
}
