<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Znck\Eloquent\Relations\BelongsToThrough;

interface BelongsToUserThroughUserProfile
{
    public function user(): BelongsToThrough;
}
