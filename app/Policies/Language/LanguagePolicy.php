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
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-language');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Language $language
     * @return bool
     */
    public function delete(User $user, Language $language): bool
    {
        return $user->can('delete-language');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-language');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Language $language
     * @return bool
     */
    public function forceDelete(User $user, Language $language): bool
    {
        return $user->can('force-delete-language');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-language');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param User $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder-language');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Language $language
     * @return bool
     */
    public function replicate(User $user, Language $language): bool
    {
        return $user->can('replicate-language');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Language $language
     * @return bool
     */
    public function restore(User $user, Language $language): bool
    {
        return $user->can('restore-language');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-language');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Language $language
     * @return bool
     */
    public function update(User $user, Language $language): bool
    {
        return $user->can('update-language');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
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
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-language');
    }
}
