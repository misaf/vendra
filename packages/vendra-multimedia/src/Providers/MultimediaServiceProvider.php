<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraMultimedia\Console\Commands\RelocateMediaCommand;
use Misaf\VendraMultimedia\Console\Commands\SeedCommand;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraMultimedia\MultimediaPlugin;
use Misaf\VendraMultimedia\Support\DefaultPathGenerator;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator as SpatieDefaultPathGenerator;

final class MultimediaServiceProvider extends PackageServiceProvider
{
    use ResolvesConfiguredPanels;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vendra-multimedia')
            ->hasTranslations()
            ->hasMigrations([
                'create_media_table',
            ])
            ->hasCommands(RelocateMediaCommand::class, SeedCommand::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('misaf/vendra-multimedia');
            });
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            if (! $this->shouldRegisterOnPanel($panel->getId(), 'vendra-multimedia')) {
                return;
            }

            $panel->plugin(MultimediaPlugin::make());
        });

        $this->registerMediaLibraryDefaults();
    }

    /**
     * Configure Media Library to use this package's classes and defaults.
     *
     * Runs in `register()` because Media Library reads `media_model` while booting.
     * Class settings respect host overrides; boolean settings are always forced.
     */
    private function registerMediaLibraryDefaults(): void
    {
        $mediaModel = Config::get('media-library.media_model');

        if ($mediaModel === null || $mediaModel === SpatieMedia::class) {
            Config::set('media-library.media_model', Multimedia::class);
        }

        $pathGenerator = Config::get('media-library.path_generator');

        if ($pathGenerator === null || $pathGenerator === SpatieDefaultPathGenerator::class) {
            Config::set('media-library.path_generator', DefaultPathGenerator::class);
        }

        // Literals, not env(): env() returns its fallback once config is cached.
        Config::set([
            'media-library.queue_conversions_by_default' => false,
            'media-library.moves_media_on_update' => true,
        ]);
    }

    public function packageBooted(): void
    {
        $this->app->make(TenantTableRegistry::class)->register('media');
        $this->app->make(TenantSeeders::class)->register('vendra-multimedia:seed', priority: 26);
        $this->app->make(TenantUsageRegistry::class)->register(
            PlanLimit::StorageMegabytesPerStore,
            fn (Model $tenant): int => (int) Multimedia::query()
                ->withoutGlobalScopes()
                ->where(resolve(TenantResolver::class)->foreignKey(), $tenant->getKey())
                ->sum('size'),
        );
        AboutCommand::add('Vendra Multimedia', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-multimedia')]);
    }
}
