<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasUserProfile
{
    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
