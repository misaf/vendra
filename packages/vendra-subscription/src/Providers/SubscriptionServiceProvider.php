<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Event;
use Misaf\VendraSubscription\Console\Commands\EnforceSubscriptionsCommand;
use Misaf\VendraSubscription\Console\Commands\RecoverSubscriptionPaymentsCommand;
use Misaf\VendraSubscription\Console\Commands\ReportSubscriptionPaymentBacklogCommand;
use Misaf\VendraSubscription\Events\SubscriptionPaymentPaid;
use Misaf\VendraSubscription\Listeners\ActivateSubscriptionOnPayment;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-subscription')
            ->hasRoute('console')
            ->hasMigrations([
                'create_subscriptions_table',
            ])
            ->hasCommand(EnforceSubscriptionsCommand::class)
            ->hasCommand(RecoverSubscriptionPaymentsCommand::class)
            ->hasCommand(ReportSubscriptionPaymentBacklogCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-subscription');
            });
    }

    public function packageBooted(): void
    {
        Event::listen(SubscriptionPaymentPaid::class, ActivateSubscriptionOnPayment::class);

        AboutCommand::add('Vendra Subscription', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-subscription')]);
    }
}
