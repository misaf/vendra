<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\UserProfile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUserProfile
{
    /**
     * Get the user that owns the profile.
     *
     * @return BelongsTo
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }
}
