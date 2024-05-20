<?php

declare(strict_types=1);

namespace App\Policies\Transaction;

use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class TransactionPolicy
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
        return $user->can('create-transaction');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(\App\Models\User $user, Transaction $transaction): bool
    {
        return $user->can('delete-transaction');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-transaction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, Transaction $transaction): bool
    {
        return $user->can('force-delete-transaction');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-transaction');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function replicate(\App\Models\User $user, Transaction $transaction): bool
    {
        return $user->can('replicate-transaction');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function restore(\App\Models\User $user, Transaction $transaction): bool
    {
        return $user->can('restore-transaction');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-transaction');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function update(\App\Models\User $user, Transaction $transaction): bool
    {
        return $user->can('update-transaction');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->can('view-transaction');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-transaction');
    }
}
