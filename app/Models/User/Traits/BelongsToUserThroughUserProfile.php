<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use App\Models\User\UserProfile;
use Znck\Eloquent\Relations\BelongsToThrough;

trait BelongsToUserThroughUserProfile
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToThrough
     */
    public function user(): BelongsToThrough
    {
        return $this->belongsToThrough(User::class, UserProfile::class);
    }
}
