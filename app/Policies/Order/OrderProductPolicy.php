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
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(\App\Models\User $user): bool
    {
        return $user->can('create-order-product');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function delete(\App\Models\User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('delete-order-product');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-order-product');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('force-delete-order-product');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-order-product');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function replicate(\App\Models\User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('replicate-order-product');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function restore(\App\Models\User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('restore-order-product');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-order-product');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param OrderProduct $orderProduct
     * @return bool
     */
    public function update(\App\Models\User $user, OrderProduct $orderProduct): bool
    {
        return $user->can('update-order-product');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-order-product');
    }
}
