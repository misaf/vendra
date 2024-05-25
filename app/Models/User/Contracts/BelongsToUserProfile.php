<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface BelongsToUserProfile
{
    /**
     * Get the latest document for the user profile.
     *
     * @return BelongsTo
     */
    public function userProfile(): BelongsTo;
}
