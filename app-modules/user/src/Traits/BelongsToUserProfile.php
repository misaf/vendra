<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Termehsoft\User\Models\UserProfile;

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
