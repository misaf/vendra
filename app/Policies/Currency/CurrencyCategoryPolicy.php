<?php

declare(strict_types=1);

namespace App\Policies\Currency;

use App\Models\Currency\CurrencyCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CurrencyCategoryPolicy
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
        return $user->can('create-currency-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function delete(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('delete-currency-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-currency-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function forceDelete(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('force-delete-currency-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-currency-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function replicate(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('replicate-currency-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function restore(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('restore-currency-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-currency-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function update(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('update-currency-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param CurrencyCategory $currencyCategory
     * @return bool
     */
    public function view(User $user, CurrencyCategory $currencyCategory): bool
    {
        return $user->can('view-currency-category');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-currency-category');
    }
}
