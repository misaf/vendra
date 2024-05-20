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
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(\App\Models\User $user): bool
    {
        return $user->can('create-faq');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function delete(\App\Models\User $user, Faq $faq): bool
    {
        return $user->can('delete-faq');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-faq');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, Faq $faq): bool
    {
        return $user->can('force-delete-faq');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-faq');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-faq');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function replicate(\App\Models\User $user, Faq $faq): bool
    {
        return $user->can('replicate-faq');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function restore(\App\Models\User $user, Faq $faq): bool
    {
        return $user->can('restore-faq');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-faq');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function update(\App\Models\User $user, Faq $faq): bool
    {
        return $user->can('update-faq');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
     * @param Faq $faq
     * @return bool
     */
    public function viewMultimedia(?User $user = null, Faq $faq): bool
    {
        return $this->view($user, $faq);
    }
}
