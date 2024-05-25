<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use App\Models\User\UserProfilePhone;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasUserProfilePhone
{
    /**
     * Get the latest document for the user profile.
     *
     * @return HasOne
     */
    public function LatestUserProfilePhone(): HasOne
    {
        return $this->hasOne(UserProfilePhone::class)->latestOfMany();
    }

    /**
     * Get the latest document for the user profile.
     *
     * @return HasMany
     */
    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(UserProfilePhone::class);
    }
}
