<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Observers;

use Misaf\VendraSupport\Observers\Concerns\MaintainsSingleFlagPerOwner;

/**
 * Keep exactly one default address per user profile. Synchronous, because the
 * flag is adjusted before the write.
 */
final class AddressObserver
{
    use MaintainsSingleFlagPerOwner;

    protected function flagColumn(): string
    {
        return 'is_default';
    }

    protected function ownerColumn(): string
    {
        return 'user_profile_id';
    }
}
