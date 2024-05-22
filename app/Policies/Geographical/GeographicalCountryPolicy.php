<?php

declare(strict_types=1);

namespace App\Policies\Geographical;

use App\Models\Geographical\GeographicalCountry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class GeographicalCountryPolicy
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
        return $user->can('create-geographical-country');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function delete(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('delete-geographical-country');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-geographical-country');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function forceDelete(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('force-delete-geographical-country');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-geographical-country');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function replicate(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('replicate-geographical-country');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function restore(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('restore-geographical-country');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-geographical-country');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function update(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('update-geographical-country');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param GeographicalCountry $geographicalCountry
     * @return bool
     */
    public function view(User $user, GeographicalCountry $geographicalCountry): bool
    {
        return $user->can('view-geographical-country');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-geographical-country');
    }
}
