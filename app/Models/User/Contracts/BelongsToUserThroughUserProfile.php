<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Znck\Eloquent\Relations\BelongsToThrough;

interface BelongsToUserThroughUserProfile
{
    /**
     * Get the latest document for the user profile.
     *
     * @return BelongsToThrough
     */
    public function user(): BelongsToThrough;
}
