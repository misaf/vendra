<?php

declare(strict_types=1);

namespace App\Policies\Product;

use App\Models\Product\ProductPrice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductPricePolicy
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
        return $user->can('create-product-price');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function delete(\App\Models\User $user, ProductPrice $productPrice): bool
    {
        return $user->can('delete-product-price');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-product-price');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, ProductPrice $productPrice): bool
    {
        return $user->can('force-delete-product-price');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-product-price');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function replicate(\App\Models\User $user, ProductPrice $productPrice): bool
    {
        return $user->can('replicate-product-price');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function restore(\App\Models\User $user, ProductPrice $productPrice): bool
    {
        return $user->can('restore-product-price');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-product-price');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function update(\App\Models\User $user, ProductPrice $productPrice): bool
    {
        return $user->can('update-product-price');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function view(?User $user = null, ProductPrice $productPrice): bool
    {
        return true;

        return $user->can('view-product-price');
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

        return $user->can('view-any-product-price');
    }

    /**
     * Determine whether the user can view the currency model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function viewCurrency(?User $user = null, ProductPrice $productPrice): bool
    {
        return $this->view($user, $productPrice);
    }

    /**
     * Determine whether the user can view the product model.
     *
     * @param \App\Models\User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function viewProduct(?User $user = null, ProductPrice $productPrice): bool
    {
        return $this->view($user, $productPrice);
    }
}
