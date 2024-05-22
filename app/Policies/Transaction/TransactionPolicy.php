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
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-transaction');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->can('delete-transaction');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-transaction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->can('force-delete-transaction');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-transaction');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function replicate(User $user, Transaction $transaction): bool
    {
        return $user->can('replicate-transaction');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->can('restore-transaction');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-transaction');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->can('update-transaction');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
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
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-transaction');
    }
}
