<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use App\Models\User\UserProfileBalance;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasUserProfileBalance
{
    /**
     * Get the latest document for the user profile.
     *
     * @return HasOne
     */
    public function LatestUserProfileBalance(): HasOne
    {
        return $this->hasOne(UserProfileBalance::class)->latestOfMany();
    }

    /**
     * Get the latest document for the user profile.
     *
     * @return HasMany
     */
    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(UserProfileBalance::class);
    }
}
