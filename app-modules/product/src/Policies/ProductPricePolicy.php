<?php

declare(strict_types=1);

namespace Termehsoft\Product\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Termehsoft\Product\Models\ProductPrice;
use Termehsoft\User\Models\User;

final class ProductPricePolicy
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
        return $user->can('create-product-price');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function delete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('delete-product-price');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-product-price');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function forceDelete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('force-delete-product-price');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-product-price');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function replicate(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('replicate-product-price');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function restore(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('restore-product-price');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-product-price');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function update(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('update-product-price');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
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
     * @param User $user
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
     * @param User $user
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
     * @param User $user
     * @param ProductPrice $productPrice
     * @return bool
     */
    public function viewProduct(?User $user = null, ProductPrice $productPrice): bool
    {
        return $this->view($user, $productPrice);
    }
}
