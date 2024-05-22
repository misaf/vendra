<?php

declare(strict_types=1);

namespace App\Policies\Permission;

use App\Models\Permission\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class RolePolicy
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
        return $user->can('create-role');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('delete-role');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-role');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can('force-delete-role');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-role');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function replicate(User $user, Role $role): bool
    {
        return $user->can('replicate-role');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->can('restore-role');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-role');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('update-role');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('view-role');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-role');
    }
}
