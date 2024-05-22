<?php

declare(strict_types=1);

namespace App\Policies\Order;

use App\Models\Order\OrderProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class OrderProductPolicy
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
        return $user->can('create-order-product');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function delete(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('delete-order-product');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-order-product');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function forceDelete(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('force-delete-order-product');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-order-product');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function replicate(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('replicate-order-product');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function restore(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('restore-order-product');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-order-product');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function update(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('update-order-product');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function view(User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('view-order-product');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-order-product');
    }
}
