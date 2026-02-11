<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraUser\Models\User;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            // if ($user->hasRole('super-admin')) {
            //     return true;
            // }

            return true;
        });
    }
}
