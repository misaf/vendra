<?php

declare(strict_types=1);

namespace App\Policies\Language;

use App\Models\Language\Language;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class LanguagePolicy
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
        return $user->can('create-language');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function delete(\App\Models\User $user, Language $language): bool
    {
        return $user->can('delete-language');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-language');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, Language $language): bool
    {
        return $user->can('force-delete-language');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-language');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-language');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function replicate(\App\Models\User $user, Language $language): bool
    {
        return $user->can('replicate-language');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function restore(\App\Models\User $user, Language $language): bool
    {
        return $user->can('restore-language');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-language');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function update(\App\Models\User $user, Language $language): bool
    {
        return $user->can('update-language');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param Language $language
     * @return bool
     */
    public function view(User $user, Language $language): bool
    {
        return $user->can('view-language');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-language');
    }
}
