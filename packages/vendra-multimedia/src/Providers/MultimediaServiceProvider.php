<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia\Providers;

use Composer\InstalledVersions;
use Filament\Panel;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Misaf\VendraMultimedia\Models\Multimedia;
use Misaf\VendraMultimedia\MultimediaPlugin;
use Misaf\VendraMultimedia\Support\DefaultPathGenerator;
use Misaf\VendraSupport\Filament\Concerns\ResolvesConfiguredPanels;
use Misaf\VendraSupport\Tenancy\TenantTableRegistry;
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
     * Make Spatie Media Library work without a published config/media-library.php:
     * point it at the Vendra multimedia classes and apply the behavioural defaults
     * this package standardises on.
     *
     * Runs in register() (not boot()) on purpose: Spatie's packageBooted() captures
     * media_model to attach the MediaObserver, so the value must be set before any
     * provider boots. The class settings keep an explicit host override untouched;
     * the boolean settings are forced because a boolean value cannot be told apart
     * from an app that deliberately chose the same value.
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
        AboutCommand::add('Vendra Multimedia', fn (): array => ['Version' => InstalledVersions::getPrettyVersion('misaf/vendra-multimedia')]);
    }
}
