<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Misaf\VendraBlog\BlogPlugin;
use Misaf\VendraBlog\Console\Commands\SeedCommand;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BlogServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-blog')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_blogs_table',
            ])
            ->hasCommands(SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-blog');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-blog')) {
                return;
            }

            $panel->plugin(BlogPlugin::make());
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('blog_post_categories', 'blog_posts');
        $this->app->make(TenantSeeders::class)->register('vendra-blog:seed', priority: 55);

        AboutCommand::add('Vendra Blog', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-blog')]);
    }
}
