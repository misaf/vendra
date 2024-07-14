<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Termehsoft\User\Models\User;

final class PulseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', fn(User $user) => $user->hasRole('super-admin'));

        Pulse::handleExceptionsUsing(function ($e): void {
            Log::debug('An exception happened in Pulse', [
                'message' => $e->getMessage(),
                'stack'   => $e->getTraceAsString(),
            ]);
        });
    }
}
