<?php

declare(strict_types=1);

namespace App\Models\Product\Policies;

use App\Models\Product\ProductCategory;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductCategoryPolicy
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
        return $user->can('create-product-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('delete-product-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-product-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function forceDelete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('force-delete-product-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-product-category');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param User $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder-product-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function replicate(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('replicate-product-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function restore(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('restore-product-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-product-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('update-product-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function view(?User $user = null, ProductCategory $productCategory): bool
    {
        return true;

        return $user->can('view-product-category');
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

        return $user->can('view-any-product-category');
    }

    /**
     * Determine whether the user can view the multimedia model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function viewMultimedia(?User $user = null, ProductCategory $productCategory): bool
    {
        return $this->view($user, $productCategory);
    }

    /**
     * Determine whether the user can view the product prices model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function viewProductPrices(?User $user = null, ProductCategory $productCategory): bool
    {
        return $this->view($user, $productCategory);
    }

    /**
     * Determine whether the user can view the products model.
     *
     * @param User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function viewProducts(?User $user = null, ProductCategory $productCategory): bool
    {
        return $this->view($user, $productCategory);
    }
}
