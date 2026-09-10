<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraTransaction\Console\Commands\VerifyWalletBalancesCommand;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\Services\TransactionGatewayRegistry;
use Misaf\VendraTransaction\Services\WalletResolver;
use Misaf\VendraTransaction\Support\TransactionUsers;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class TransactionServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-transaction')
            ->hasTranslations()
            ->hasMigrations([
                'create_transactions_table',
            ])
            ->hasCommands(VerifyWalletBalancesCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-transaction');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WalletResolver::class);
        $this->app->singleton(TransactionGatewayRegistry::class);

        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-transaction')) {
                return;
            }

            $panel
                ->discoverResources(
                    in: __DIR__.'/../Filament/Clusters/Resources',
                    for: 'Misaf\\VendraTransaction\\Filament\\Clusters\\Resources',
                )
                ->discoverWidgets(
                    in: __DIR__.'/../Filament/Widgets',
                    for: 'Misaf\\VendraTransaction\\Filament\\Widgets',
                );
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('transaction_gateways', 'wallets', 'transactions');

        AboutCommand::add('Vendra Transaction', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-transaction')]);

        $this->registerUserRelationships();
    }

    /**
     * Attaches wallet relations to the host application's configured user
     * model without depending on a concrete user package.
     */
    private function registerUserRelationships(): void
    {
        $userModel = TransactionUsers::model();

        $userModel::resolveRelationUsing(
            'wallets',
            fn (Model $user): HasMany => $user->hasMany(Wallet::class, 'user_id'),
        );
    }
}
