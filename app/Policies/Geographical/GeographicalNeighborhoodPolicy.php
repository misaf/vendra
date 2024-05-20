<?php

declare(strict_types=1);

namespace App\Policies\Geographical;

use App\Models\Geographical\GeographicalNeighborhood;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class GeographicalNeighborhoodPolicy
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
        return $user->can('create-geographical-neighborhood');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function delete(\App\Models\User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('delete-geographical-neighborhood');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-geographical-neighborhood');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('force-delete-geographical-neighborhood');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-geographical-neighborhood');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function replicate(\App\Models\User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('replicate-geographical-neighborhood');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function restore(\App\Models\User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('restore-geographical-neighborhood');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-geographical-neighborhood');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function update(\App\Models\User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('update-geographical-neighborhood');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param GeographicalNeighborhood $geographicalNeighborhood
     * @return bool
     */
    public function view(User $user, GeographicalNeighborhood $geographicalNeighborhood): bool
    {
        return $user->can('view-geographical-neighborhood');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-geographical-neighborhood');
    }
}
