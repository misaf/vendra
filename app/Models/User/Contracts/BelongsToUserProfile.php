<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface BelongsToUserProfile
{
    public function userProfile(): BelongsTo;
}
