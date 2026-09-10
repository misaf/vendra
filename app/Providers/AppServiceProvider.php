<?php

declare(strict_types=1);

namespace App\Providers;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Http\Discovery\Psr17Factory;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Mcp\Server;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraReseller\Models\ResellerUser;
use Misaf\VendraReseller\Support\TransactionSubscriptionCharger;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Observers\StoreDomainObserver;
use Misaf\VendraStore\Services\ContainerStorefrontProvisioner;
use Misaf\VendraStore\Support\StorefrontSettings;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Contracts\SubscriptionCharger;
use Misaf\VendraUser\Models\User;
use Symfony\AI\McpBundle\Controller\McpController;
use Symfony\AI\McpBundle\Http\MiddlewareFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);
        $this->app->bind(ResetPassword::class, ResetPasswordNotification::class);
        $this->app->bind(VerifyEmail::class, VerifyEmailNotification::class);

        // vendra-support owns the SubscriptionCharger contract and its null default;
        // vendra-transaction stays unaware of subscription semantics. As the
        // composition root, the host app supplies the transaction-backed adapter
        // and binds it over the null charger.
        $this->app->singleton(SubscriptionCharger::class, TransactionSubscriptionCharger::class);

        $this->registerStorefrontProvisioning();
    }

    public function boot(): void
    {
        DevCommands::artisan('queue:listen --queue=default,transactional-email,storefronts --tries=1 --timeout=0', 'queue');
        DevCommands::except('server', 'vite', 'horizon');

        Relation::morphMap([
            'reseller' => Reseller::class,
            'reseller_user' => ResellerUser::class,
        ]);

        /*
         | The registry drives the `vendra-tenant:enable` retrofit, which
         | backfills every null tenant id and then forces the column NOT NULL.
         | Nothing the host owns wants that: `storefront_deployments` describes
         | the store itself and is keyed by `store_id`, and `settings` keeps a
         | null `tenant_id` on purpose for its platform-wide rows. Register a
         | table here only when a non-null `tenant_id` is the right end state.
         */

        URL::forceScheme('https');
        Model::shouldBeStrict();
        DB::prohibitDestructiveCommands(app()->isProduction());
        Password::defaults(fn () => Password::min(8));

        StoreDomain::observe(StoreDomainObserver::class);

        $this->registerMcpControllerCompatibility();
        $this->configureRateLimiting();
        $this->configureAuthLogging();
    }

    /**
     * api-platform/laravel wires the Symfony MCP controller with the PSR-17
     * factories of two more signatures ago, but symfony/mcp-bundle 0.12 added a
     * required MiddlewareFactory argument to its constructor. Rebind the
     * controller here so the streamable transport keeps its configurable
     * DNS-rebinding middleware stack instead of failing to build.
     */
    private function registerMcpControllerCompatibility(): void
    {
        $this->app->booted(function (Application $app): void {
            $psr17Factory = new Psr17Factory;
            $psrHttpFactory = new PsrHttpFactory(
                $psr17Factory,
                $psr17Factory,
                $psr17Factory,
                $psr17Factory,
            );

            $app->singleton(McpController::class, static fn (Application $application): McpController => new McpController(
                $application->make(Server::class),
                $psrHttpFactory,
                new HttpFoundationFactory,
                $psr17Factory,
                $psr17Factory,
                new MiddlewareFactory,
            ));
        });
    }

    /**
     * The storefront provisioning adapter and the settings it reads.
     *
     * Bound rather than shared: the configuration is read on each resolve, so a
     * changed endpoint or image takes effect without a rebuilt container.
     */
    private function registerStorefrontProvisioning(): void
    {
        $this->app->bind(StorefrontSettings::class, static fn (): StorefrontSettings => StorefrontSettings::fromConfig());

        // The platform runs the storefront containers itself through the
        // default driver managed by misaf/laravel-docker-engine.
        $this->app->bind(StorefrontProvisioner::class, ContainerStorefrontProvisioner::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('mcp', static function (Request $request): Limit {
            $actor = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(60)->by(
                is_int($actor) || is_string($actor) ? "user:{$actor}" : "ip:{$request->ip()}",
            );
        });
    }

    private function configureAuthLogging(): void
    {
        Event::listen(Authenticated::class, static function (Authenticated $event): void {
            $actorId = $event->user->getAuthIdentifier();

            new RequestJobContext(
                actorId: is_int($actorId) || is_string($actorId) ? $actorId : null,
                actorType: $event->guard,
            )->add();
        });

        Event::listen(Failed::class, static function (Failed $event): void {
            new RequestJobContext(
                operation: 'auth_failed',
                actorType: $event->guard,
            )->scope(static fn () => Log::warning('Authentication attempt failed.'));
        });

        Event::listen(Lockout::class, static function (Lockout $event): void {
            new RequestJobContext(operation: 'auth_lockout')
                ->scope(static fn () => Log::warning('Authentication lockout triggered.'));
        });
    }
}
