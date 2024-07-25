<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Termehsoft\User\Models\User;
use Termehsoft\User\Models\UserProfile;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

trait BelongsToUserThroughUserProfile
{
    use TraitsBelongsToThrough;

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
