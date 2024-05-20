<?php

declare(strict_types=1);

namespace App\Policies\Product;

use App\Models\Product\ProductCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductCategoryPolicy
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
        return $user->can('create-product-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function delete(\App\Models\User $user, ProductCategory $productCategory): bool
    {
        return $user->can('delete-product-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-product-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, ProductCategory $productCategory): bool
    {
        return $user->can('force-delete-product-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-product-category');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-product-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function replicate(\App\Models\User $user, ProductCategory $productCategory): bool
    {
        return $user->can('replicate-product-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function restore(\App\Models\User $user, ProductCategory $productCategory): bool
    {
        return $user->can('restore-product-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-product-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function update(\App\Models\User $user, ProductCategory $productCategory): bool
    {
        return $user->can('update-product-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
     * @param ProductCategory $productCategory
     * @return bool
     */
    public function viewProducts(?User $user = null, ProductCategory $productCategory): bool
    {
        return $this->view($user, $productCategory);
    }
}
