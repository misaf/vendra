<?php

declare(strict_types=1);

namespace App\Policies\Faq;

use App\Models\Faq\Faq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class FaqPolicy
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
        return $user->can('create-faq');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('delete-faq');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-faq');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->can('force-delete-faq');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-faq');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param User $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder-faq');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function replicate(User $user, Faq $faq): bool
    {
        return $user->can('replicate-faq');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function restore(User $user, Faq $faq): bool
    {
        return $user->can('restore-faq');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-faq');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function update(User $user, Faq $faq): bool
    {
        return $user->can('update-faq');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function view(?User $user = null, Faq $faq): bool
    {
        return true;

        return $user->can('view-faq');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(?User $user = null): bool
    {
        return true;

        return $user->can('view-any-faq');
    }

    /**
     * Determine whether the user can view the faq category model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function viewFaqCategory(?User $user = null, Faq $faq): bool
    {
        return $this->view($user, $faq);
    }

    /**
     * Determine whether the user can view the multimedia model.
     *
     * @param User $user
     * @param Faq $faq
     * @return bool
     */
    public function viewMultimedia(?User $user = null, Faq $faq): bool
    {
        return $this->view($user, $faq);
    }
}
