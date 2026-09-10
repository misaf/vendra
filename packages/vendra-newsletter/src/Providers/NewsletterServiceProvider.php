<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraNewsletter\Console\Commands\SeedCommand;
use Misaf\VendraNewsletter\Console\Commands\SendScheduledNewslettersCommand;
use Misaf\VendraNewsletter\NewsletterPlugin;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class NewsletterServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-newsletter')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasRoute('web')
            ->hasMigrations([
                'create_newsletters_table',
            ])
            ->hasCommands(
                SeedCommand::class,
                SendScheduledNewslettersCommand::class,
            )
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-newsletter');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-newsletter')) {
                return;
            }

            $panel->plugin(NewsletterPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('newsletters', 'newsletter_subscribers');
        $this->app->make(TenantSeeders::class)->register('vendra-newsletter:seed', priority: 70);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            if (! Config::boolean('vendra-newsletter.schedule.enabled', true)) {
                return;
            }

            $schedule->command(SendScheduledNewslettersCommand::class)
                ->cron(Config::string('vendra-newsletter.schedule.cron', '* * * * *'))
                ->withoutOverlapping();
        });

        AboutCommand::add('Vendra Newsletter', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-newsletter')]);
    }
}
