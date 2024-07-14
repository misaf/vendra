<?php

declare(strict_types=1);

namespace Termehsoft\Product\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Termehsoft\Product\Models\Product;
use Termehsoft\User\Models\User;

final class ProductPolicy
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
        return $user->can('create-product');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete-product');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-product');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can('force-delete-product');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-product');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function replicate(User $user, Product $product): bool
    {
        return $user->can('replicate-product');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->can('restore-product');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-product');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('update-product');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function view(?User $user = null, Product $product): bool
    {
        return true;

        return $user->can('view-product');
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

        return $user->can('view-any-product');
    }

    /**
     * Determine whether the user can view the multimedia model.
     */
    public function viewMultimedia(?User $user = null, Product $product): bool
    {
        return $this->view($user, $product);
    }

    /**
     * Determine whether the user can view the product category model.
     */
    public function viewProductCategory(?User $user = null, Product $product): bool
    {
        return $this->view($user, $product);
    }

    /**
     * Determine whether the user can view the product price model.
     */
    public function viewProductPrice(?User $user = null, Product $product): bool
    {
        return $this->view($user, $product);
    }

    /**
     * Determine whether the user can view the product prices model.
     */
    public function viewProductPrices(?User $user = null, Product $product): bool
    {
        return $this->view($user, $product);
    }
}
