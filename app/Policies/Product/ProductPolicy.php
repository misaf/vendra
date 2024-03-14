<?php

declare(strict_types=1);

namespace App\Policies\Product;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can('create-product');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('delete-product');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-product');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->can('force-delete-product');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-product');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder-product');
    }

    public function replicate(User $user, Product $product): bool
    {
        return $user->can('replicate-product');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can('restore-product');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-product');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('update-product');
    }

    public function view(?User $user, Product $product): bool
    {
        return true;

        return $user->can('view-product');
    }

    public function viewAny(?User $user): bool
    {
        return true;

        return $user->can('view-any-product');
    }

    public function viewMultimedia(?User $user, Product $product)
    {
        return $this->view($user, $product);
    }

    public function viewProductCategory(?User $user, Product $product)
    {
        return $this->view($user, $product);
    }

    public function viewProductPrice(?User $user, Product $product)
    {
        return $this->view($user, $product);
    }
}
