<?php

declare(strict_types=1);

namespace App\Policies\User;

use App\Models\User;
use App\Models\User\UserProfilePhone;
use Illuminate\Auth\Access\HandlesAuthorization;

final class UserProfilePhonePolicy
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
        return $user->can('create-user-profile-phone');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function delete(\App\Models\User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('delete-user-profile-phone');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-user-profile-phone');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('force-delete-user-profile-phone');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-user-profile-phone');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function replicate(\App\Models\User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('replicate-user-profile-phone');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function restore(\App\Models\User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('restore-user-profile-phone');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-user-profile-phone');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function update(\App\Models\User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('update-user-profile-phone');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param UserProfilePhone $userProfilePhone
     * @return bool
     */
    public function view(User $user, UserProfilePhone $userProfilePhone): bool
    {
        return $user->can('view-user-profile-phone');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-user-profile-phone');
    }
}
