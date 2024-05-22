<?php

declare(strict_types=1);

namespace App\Policies\Permission;

use App\Models\Permission\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class PermissionPolicy
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
        return $user->can('create-permission');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('delete-permission');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-permission');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->can('force-delete-permission');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-permission');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function replicate(User $user, Permission $permission): bool
    {
        return $user->can('replicate-permission');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->can('restore-permission');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-permission');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can('update-permission');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can('view-permission');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-permission');
    }
}
