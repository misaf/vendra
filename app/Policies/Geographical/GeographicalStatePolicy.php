<?php

declare(strict_types=1);

namespace App\Policies\Geographical;

use App\Models\Geographical\GeographicalState;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class GeographicalStatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-geographical-state');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function delete(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('delete-geographical-state');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-geographical-state');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function forceDelete(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('force-delete-geographical-state');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-geographical-state');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function replicate(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('replicate-geographical-state');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function restore(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('restore-geographical-state');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-geographical-state');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function update(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('update-geographical-state');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param GeographicalState $geographicalState
     * @return bool
     */
    public function view(User $user, GeographicalState $geographicalState): bool
    {
        return $user->can('view-geographical-state');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-geographical-state');
    }
}
