<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Misaf\VendraAffiliate\AffiliatePlugin;
use Misaf\VendraAffiliate\Console\Commands\SeedCommand;
use Misaf\VendraAffiliate\Listeners\RegistrationSubscriber;
use Misaf\VendraAffiliate\Listeners\TransactionCommissionSubscriber;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraAffiliate\Services\AffiliateCodeService;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraUser\Models\User;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AffiliateServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-affiliate')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_affiliates_table',
            ])
            ->hasRoute('web')
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-affiliate');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AffiliateCodeService::class);

        Panel::configureUsing(function (Panel $panel): void {
            if (
                ! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-affiliate')
                && ! in_array($panel->getId(), Config::array('vendra-affiliate.user_panels'), true)
            ) {
                return;
            }

            $panel->plugin(AffiliatePlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register(
            'affiliates',
            'affiliate_clicks',
            'affiliate_referrals',
            'affiliate_payouts',
            'affiliate_commissions',
        );
        $this->app->make(TenantSeeders::class)->register('vendra-affiliate:seed', priority: 75);

        AboutCommand::add('Vendra Affiliate', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-affiliate')]);

        Event::subscribe(RegistrationSubscriber::class);
        Event::subscribe(TransactionCommissionSubscriber::class);

        User::resolveRelationUsing(
            'affiliate',
            fn (User $user): HasOne => $user->hasOne(Affiliate::class),
        );

        User::resolveRelationUsing(
            'affiliateReferral',
            fn (User $user): HasOne => $user->hasOne(AffiliateReferral::class),
        );
    }
}
