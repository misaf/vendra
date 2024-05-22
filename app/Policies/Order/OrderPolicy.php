<?php

declare(strict_types=1);

namespace App\Policies\Order;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class OrderPolicy
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
        return $user->can('create-order');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('delete-order');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-order');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can('force-delete-order');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-order');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function replicate(User $user, Order $order): bool
    {
        return $user->can('replicate-order');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->can('restore-order');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-order');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('update-order');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function view(User $user, Order $order): bool
    {
        return $user->can('view-order');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-order');
    }
}
