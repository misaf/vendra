<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Event;
use Misaf\VendraSubscription\Console\Commands\EnforceSubscriptionsCommand;
use Misaf\VendraSubscription\Console\Commands\RecoverSubscriptionPaymentsCommand;
use Misaf\VendraSubscription\Console\Commands\ReportSubscriptionPaymentBacklogCommand;
use Misaf\VendraSubscription\Contracts\BillingProfile;
use Misaf\VendraSubscription\Contracts\PlanUsageGuard;
use Misaf\VendraSubscription\Contracts\SubscriptionUnitSuspender;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;
use Misaf\VendraSubscription\Listeners\ActivateSubscriptionOnPayment;
use Misaf\VendraSubscription\Listeners\IssueInvoiceOnPayment;
use Misaf\VendraSubscription\Support\NullBillingProfile;
use Misaf\VendraSubscription\Support\NullPlanUsageGuard;
use Misaf\VendraSubscription\Support\NullSubscriptionUnitSuspender;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-subscription')
            ->hasTranslations()
            ->hasViews()
            ->hasRoute('console')
            ->hasMigrations([
                'create_subscriptions_table',
            ])
            ->hasConsoleCommands(
                EnforceSubscriptionsCommand::class,
                RecoverSubscriptionPaymentsCommand::class,
                ReportSubscriptionPaymentBacklogCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-subscription');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singletonIf(SubscriptionUnitSuspender::class, NullSubscriptionUnitSuspender::class);
        $this->app->singletonIf(PlanUsageGuard::class, NullPlanUsageGuard::class);
        $this->app->singletonIf(BillingProfile::class, NullBillingProfile::class);
    }

    public function packageBooted(): void
    {
        Event::listen(SubscriptionPaymentPaid::class, ActivateSubscriptionOnPayment::class);
        Event::listen(SubscriptionPaymentPaid::class, IssueInvoiceOnPayment::class);

        AboutCommand::add('Vendra Subscription', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-subscription')]);
    }
}
