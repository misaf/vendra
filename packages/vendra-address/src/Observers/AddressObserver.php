<?php

declare(strict_types=1);

namespace Misaf\VendraAddress\Observers;

use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraAddress\Models\Address;

/**
 * Keep exactly one default address per user profile.
 *
 * Synchronous: `creating` and `saving` adjust the flag before the write. A
 * profile's first address becomes its default, flagging another one clears the
 * rest, and when the default is deleted the oldest remaining address takes
 * over. A soft-deleted default gives up the flag, so restoring it does not take
 * the default back.
 */
final class AddressObserver
{
    public function creating(Address $address): void
    {
        if (! $this->siblings($address)->exists()) {
            $address->is_default = true;
        }
    }

    public function saving(Address $address): void
    {
        if ($address->is_default) {
            $this->siblings($address)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            return;
        }

        if ($address->exists && $address->getOriginal('is_default') === true
            && ! $this->siblings($address)->where('is_default', true)->exists()) {
            $address->is_default = true;
        }
    }

    public function deleted(Address $address): void
    {
        if (! $address->is_default) {
            return;
        }

        if ($address->exists) {
            $address->forceFill(['is_default' => false])->saveQuietly();
        }

        $this->siblings($address)->oldest('id')->first()?->update(['is_default' => true]);
    }

    /**
     * @return Builder<Address>
     */
    private function siblings(Address $address): Builder
    {
        return Address::query()
            ->where('user_profile_id', $address->user_profile_id)
            ->when($address->exists, fn (Builder $query) => $query->whereKeyNot($address->getKey()));
    }
}
