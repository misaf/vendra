<?php

declare(strict_types=1);

namespace App\Policies\Currency;

use App\Models\Currency\Currency;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CurrencyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(\App\Models\User $user): bool
    {
        return $user->can('create-currency');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function delete(\App\Models\User $user, Currency $currency): bool
    {
        return $user->can('delete-currency');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-currency');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, Currency $currency): bool
    {
        return $user->can('force-delete-currency');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-currency');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-currency');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function replicate(\App\Models\User $user, Currency $currency): bool
    {
        return $user->can('replicate-currency');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function restore(\App\Models\User $user, Currency $currency): bool
    {
        return $user->can('restore-currency');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-currency');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function update(\App\Models\User $user, Currency $currency): bool
    {
        return $user->can('update-currency');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param Currency $currency
     * @return bool
     */
    public function view(User $user, Currency $currency): bool
    {
        return $user->can('view-currency');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-currency');
    }
}
