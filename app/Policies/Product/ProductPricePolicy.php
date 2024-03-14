<?php

declare(strict_types=1);

namespace App\Policies\Product;

use App\Models\Product\ProductPrice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductPricePolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can('create-product-price');
    }

    public function delete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('delete-product-price');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-product-price');
    }

    public function forceDelete(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('force-delete-product-price');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-product-price');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder-product-price');
    }

    public function replicate(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('replicate-product-price');
    }

    public function restore(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('restore-product-price');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-product-price');
    }

    public function update(User $user, ProductPrice $productPrice): bool
    {
        return $user->can('update-product-price');
    }

    public function view(?User $user, ProductPrice $productPrice): bool
    {
        return true;

        return $user->can('view-product-price');
    }

    public function viewAny(?User $user): bool
    {
        return true;

        return $user->can('view-any-product-price');
    }

    public function viewCurrency(?User $user, ProductPrice $productPrice)
    {
        return $this->view($user, $productPrice);
    }

    public function viewProduct(?User $user, ProductPrice $productPrice)
    {
        return $this->view($user, $productPrice);
    }
}
