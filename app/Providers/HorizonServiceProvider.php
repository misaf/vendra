<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

final class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();

        Horizon::routeMailNotificationsTo(config('mail.from.address'));
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn(User $user): bool => $user->hasRole('super-admin'));
    }
}
